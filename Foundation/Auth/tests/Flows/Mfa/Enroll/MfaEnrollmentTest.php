<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Mfa\Enroll;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Mfa\Backup\GenerateBackupCodes;
use Avax\Auth\System\Flow\Mfa\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flow\Mfa\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\InMemoryMfaStore;
use Avax\Auth\System\Flow\Mfa\MfaEnrollmentFailed;
use Avax\Auth\System\Flow\Mfa\MfaStatus;
use Avax\Auth\System\Flow\Mfa\Totp;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MFA enrollment flow.
 */
final class MfaEnrollmentTest extends TestCase
{
    public function testEnrollmentStartsAndCompletesOnlyAfterValidTotpProof() : void
    {
        $clock                 = new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'));
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
        $this->assertSame(MfaStatus::ENROLLMENT_PENDING, $enrollment->status);
        $this->assertStringStartsWith('otpauth://totp/', $enrollment->otpauthUri);
        $this->assertFalse($mfaStore->isEnabled(new \Avax\Auth\System\Capability\User\UserId(1)));

        $backupCodes = $confirm->execute(new ConfirmMfaEnrollmentData(
                                             code: $totp->codeAt($enrollment->secret(), $clock->now())
                                         ));

        $this->assertCount(10, $backupCodes->codes);
        $this->assertTrue($mfaStore->isEnabled(new \Avax\Auth\System\Capability\User\UserId(1)));
        $this->assertTrue($currentAuthentication->read()->user()?->mfaEnabled);
        $this->assertNotNull($currentAuthentication->read()->mfaVerifiedAt());
        $this->assertSame([
                              'auth.mfa.enrollment.started',
                              'auth.mfa.enrollment.completed',
                              'auth.mfa.enabled',
                          ], array_map(
                              static fn ($event) : string => $event->name,
                              $auditLog->events()
                          ));
    }

    private function authenticatedContext() : CurrentAuthentication
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id      : 1,
                      email   : 'user@example.com',
                      username: 'user'
                  ),
            mode: AuthenticationMode::TOKEN
        ));

        return $currentAuthentication;
    }

    public function testEnrollmentRejectsInvalidFirstCodeAndDoesNotHalfEnableMfa() : void
    {
        $clock                 = new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'));
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
            $confirm->execute(new ConfirmMfaEnrollmentData('000000'));
        } finally {
            $this->assertFalse($mfaStore->isEnabled(new \Avax\Auth\System\Capability\User\UserId(1)));
            $this->assertSame(MfaStatus::ENROLLMENT_PENDING, $mfaStore->status(new \Avax\Auth\System\Capability\User\UserId(1)));
        }
    }

    public function testEnrollmentCanBeCancelledSafely() : void
    {
        $clock                 = new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'));
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

        $this->assertSame(MfaStatus::DISABLED, $mfaStore->status(new \Avax\Auth\System\Capability\User\UserId(1)));
    }

    public function testEnrollmentExpires() : void
    {
        $clock                 = new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'));
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
        $clock->advance(new DateInterval('PT16M'));

        $this->expectException(MfaEnrollmentFailed::class);
        $this->expectExceptionMessage('MFA enrollment has expired.');

        $confirm->execute(new ConfirmMfaEnrollmentData(
                              code: $totp->codeAt($enrollment->secret(), $clock->now())
                          ));
    }
}
