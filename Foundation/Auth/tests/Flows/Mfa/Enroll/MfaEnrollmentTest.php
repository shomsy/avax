<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Mfa\Enroll;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\GenerateBackupCodes;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaStatus;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaEnrollmentFailed;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Totp\Totp;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\Tests\Support\FrozenClock;
use Avax\Tests\TestCase;
use DateInterval;
use DateMalformedStringException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MFA enrollment flow.
 */
final class MfaEnrollmentTest extends TestCase
{
    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function testEnrollmentStartsAndCompletesOnlyAfterValidTotpProof() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'));
        $currentAuthentication = $this->authenticatedContext();
        $mfaStore              = new InMemoryMfaStore();
        $auditLog              = new InMemoryAuditLog();
        $totp                  = new Totp();

        $start   = new StartMfaEnrollment(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            totp                 : $totp,
            auditLog             : $auditLog,
            clock                : $clock,
            issuer               : 'Acme Auth'
        );
        $confirm = new ConfirmMfaEnrollment(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            totp                 : $totp,
            generateBackupCodes  : new GenerateBackupCodes(
                                       passwordHasher: new PasswordHasher(),
                                       clock         : $clock
                                   ),
            auditLog             : $auditLog,
            clock                : $clock
        );

        $enrollment = $start->execute();
        $this->assertSame(expected: MfaStatus::ENROLLMENT_PENDING, actual: $enrollment->status);
        $this->assertStringStartsWith(prefix: 'otpauth://totp/', string: $enrollment->otpauthUri);
        $this->assertFalse(condition: $mfaStore->isEnabled(userId: new UserId(value: 1)));

        $backupCodes = $confirm->execute(data: new ConfirmMfaEnrollmentData(
                                                   code: $totp->codeAt(secret: $enrollment->secret(), moment: $clock->now())
                                               ));

        $this->assertCount(expectedCount: 10, haystack: $backupCodes->codes);
        $this->assertTrue(condition: $mfaStore->isEnabled(userId: new UserId(value: 1)));
        $this->assertTrue(condition: $currentAuthentication->read()->user()?->mfaEnabled);
        $this->assertNotNull(actual: $currentAuthentication->read()->mfaVerifiedAt());
        $this->assertSame(expected: [
                                        'auth.mfa.enrollment.started',
                                        'auth.mfa.enrollment.completed',
                                        'auth.mfa.enabled',
                                    ], actual: array_map(
            callback: static fn ($event) : string => $event->name,
            array   : $auditLog->events()
                                    ));
    }

    private function authenticatedContext() : CurrentAuthentication
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id      : 1,
                      email   : 'user@example.com',
                      username: 'user'
                  ),
            mode: AuthenticationMode::TOKEN
        ));

        return $currentAuthentication;
    }

    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function testEnrollmentRejectsInvalidFirstCodeAndDoesNotHalfEnableMfa() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'));
        $currentAuthentication = $this->authenticatedContext();
        $mfaStore              = new InMemoryMfaStore();
        $totp                  = new Totp();

        $start   = new StartMfaEnrollment(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            totp                 : $totp,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        );
        $confirm = new ConfirmMfaEnrollment(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            totp                 : $totp,
            generateBackupCodes  : new GenerateBackupCodes(
                                       passwordHasher: new PasswordHasher(),
                                       clock         : $clock
                                   ),
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        );

        $start->execute();

        $this->expectException(MfaEnrollmentFailed::class);
        $this->expectExceptionMessage('MFA code is invalid.');

        try {
            $confirm->execute(data: new ConfirmMfaEnrollmentData(code: '000000'));
        } finally {
            $this->assertFalse(condition: $mfaStore->isEnabled(userId: new UserId(value: 1)));
            $this->assertSame(expected: MfaStatus::ENROLLMENT_PENDING, actual: $mfaStore->status(userId: new UserId(value: 1)));
        }
    }

    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function testEnrollmentCanBeCancelledSafely() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'));
        $currentAuthentication = $this->authenticatedContext();
        $mfaStore              = new InMemoryMfaStore();

        $start  = new StartMfaEnrollment(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            totp                 : new Totp(),
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        );
        $cancel = new CancelMfaEnrollment(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        );

        $start->execute();
        $cancel->execute();

        $this->assertSame(expected: MfaStatus::DISABLED, actual: $mfaStore->status(userId: new UserId(value: 1)));
    }

    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function testEnrollmentExpires() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'));
        $currentAuthentication = $this->authenticatedContext();
        $mfaStore              = new InMemoryMfaStore();
        $totp                  = new Totp();

        $start   = new StartMfaEnrollment(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            totp                 : $totp,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        );
        $confirm = new ConfirmMfaEnrollment(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            totp                 : $totp,
            generateBackupCodes  : new GenerateBackupCodes(
                                       passwordHasher: new PasswordHasher(),
                                       clock         : $clock
                                   ),
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        );

        $enrollment = $start->execute();
        $clock->advance(interval: new DateInterval(duration: 'PT16M'));

        $this->expectException(MfaEnrollmentFailed::class);
        $this->expectExceptionMessage('MFA enrollment has expired.');

        $confirm->execute(data: new ConfirmMfaEnrollmentData(
                                    code: $totp->codeAt(secret: $enrollment->secret(), moment: $clock->now())
                                ));
    }
}
