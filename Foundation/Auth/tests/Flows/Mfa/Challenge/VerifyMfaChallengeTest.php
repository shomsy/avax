<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Mfa\Challenge;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\IssuedAuthentication;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\GenerateBackupCodes;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\VerifyBackupCode;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\InMemoryAttemptLimitStorage;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\LimitMfaAttempts;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\MfaChallengeRecord;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaChallengePurpose;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaMethod;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallengeFailed;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallengeFailure;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallengePurpose;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaMethod;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaMethodRecord;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Totp;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\InMemoryAttemptLimitStorage;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\LimitMfaAttempts;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\MfaChallengeRecord;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\VerifyMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\VerifyMfaChallengeData;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\IssuedRefreshToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\IssuedToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateInvalidOperationException;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use ReflectionProperty;
use RuntimeException;

/**
 * Unit tests for MFA challenge verification.
 */
final class VerifyMfaChallengeTest extends TestCase
{
    /**
     * @throws DateInvalidOperationException
     */
    public function testValidTotpCompletesAuthentication() : void
    {
        [$flow, $totp, $clock, $challengeStore, $currentAuthentication] = $this->makeFlow();
        $challengeStore->issue(record: $this->challengeRecord(expiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT5M'))));

        $result = $flow->execute(data: new VerifyMfaChallengeData(
                                           challengeId: 'challenge-1',
                                           code       : $totp->codeAt(secret: 'SECRETSECRETSECRETSECRETSECRETSE', moment: $clock->now())
                                       ));

        $this->assertTrue(condition: $result->isAuthenticated());
        $this->assertNotNull(actual: $result->context()->mfaVerifiedAt());
        $this->assertTrue(condition: $currentAuthentication->read()->isAuthenticated());
        $this->assertNull(actual: $challengeStore->find(challengeId: 'challenge-1'));
    }

    /**
     * @return array{0: VerifyMfaChallenge, 1: Totp, 2: FrozenClock, 3: InMemoryMfaChallengeStore, 4:
     *                  CurrentAuthentication}
     */
    private function makeFlow(LimitMfaAttempts|null $attemptLimit = null) : array
    {
        $clock      = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'));
        $userSource = new InMemoryUserSource();
        $user       = User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'user@example.com'),
            username    : 'user',
            passwordHash: 'hash'
        );
        $userSource->create(user: $user);

        $mfaStore = new InMemoryMfaStore();
        $mfaStore->saveMethod(record: new MfaMethodRecord(
                                          userId   : new UserId(value: 1),
                                          method   : MfaMethod::TOTP,
                                          secret   : 'SECRETSECRETSECRETSECRETSECRETSE',
                                          enabledAt: $clock->now()
                                      ));
        $challengeStore        = new InMemoryMfaChallengeStore();
        $currentAuthentication = new CurrentAuthentication();
        $totp                  = new Totp();
        $identity              = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('issue')->andReturnUsing(
            function (User $issuedUser, DateTimeImmutable $verifiedAt) : IssuedAuthentication {
                return new IssuedAuthentication(
                    mode         : AuthenticationMode::TOKEN,
                    accessToken  : new IssuedToken(
                                       token    : 'access-token',
                                       tokenId  : 'access-id',
                                       expiresAt: $verifiedAt->modify(modifier: '+1 hour')
                                   ),
                    refreshToken : new IssuedRefreshToken(
                                       token        : 'refresh-token',
                                       tokenId      : 'refresh-id',
                                       familyId     : 'family-id',
                                       userId       : $issuedUser->getId(),
                                       expiresAt    : $verifiedAt->modify(modifier: '+30 days'),
                                       mfaVerifiedAt: $verifiedAt
                                   ),
                    mfaVerifiedAt: $verifiedAt
                );
            }
        );
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $flow = new VerifyMfaChallenge(
            challengeStore          : $challengeStore,
            mfaStore                : $mfaStore,
            totp                    : $totp,
            verifyBackupCode        : new VerifyBackupCode(
                                          mfaStore      : $mfaStore,
                                          passwordHasher: new PasswordHasher(),
                                          auditLog      : new InMemoryAuditLog(),
                                          clock         : $clock
                                      ),
            userSource              : $userSource,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : $mfaStore
                                      ),
            currentAuthentication   : $currentAuthentication,
            auditLog                : new InMemoryAuditLog(),
            clock                   : $clock,
            attemptLimit            : $attemptLimit ?? new LimitMfaAttempts(
            storage     : new InMemoryAttemptLimitStorage(),
            clock       : $clock,
            maxAttempts : 5,
            decaySeconds: 300
        )
        );

        return [$flow, $totp, $clock, $challengeStore, $currentAuthentication];
    }

    /**
     * @throws DateInvalidOperationException
     */
    private function challengeRecord(DateTimeImmutable $expiresAt, string $challengeId = 'challenge-1') : MfaChallengeRecord
    {
        return new MfaChallengeRecord(
            challengeId: $challengeId,
            userId     : new UserId(value: 1),
            purpose    : MfaChallengePurpose::LOGIN,
            createdAt  : $expiresAt->sub(interval: new DateInterval(duration: 'PT1M')),
            expiresAt  : $expiresAt
        );
    }

    /**
     * @throws DateInvalidOperationException
     */
    public function testInvalidTotpFails() : void
    {
        [$flow, , $clock, $challengeStore] = $this->makeFlow();
        $challengeStore->issue(record: $this->challengeRecord(expiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT5M'))));

        try {
            $flow->execute(data: new VerifyMfaChallengeData(challengeId: 'challenge-1', code: '000000'));
            self::fail(message: 'Expected MFA challenge failure.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(expected: MfaChallengeFailure::INVALID, actual: $exception->reason());
        }
    }

    /**
     * @throws DateInvalidOperationException
     */
    public function testExpiredChallengeFails() : void
    {
        [$flow, $totp, $clock, $challengeStore] = $this->makeFlow();
        $challengeStore->issue(record: $this->challengeRecord(expiresAt: $clock->now()->sub(interval: new DateInterval(duration: 'PT1M'))));

        try {
            $flow->execute(data: new VerifyMfaChallengeData(
                                     challengeId: 'challenge-1',
                                     code       : $totp->codeAt(secret: 'SECRETSECRETSECRETSECRETSECRETSE', moment: $clock->now())
                                 ));
            self::fail(message: 'Expected MFA challenge failure.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(expected: MfaChallengeFailure::EXPIRED, actual: $exception->reason());
        }
    }

    /**
     * @throws DateInvalidOperationException
     */
    public function testRepeatedFailuresBecomeLocked() : void
    {
        $limitClock = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'));
        [$flow, , $clock, $challengeStore] = $this->makeFlow(attemptLimit: new LimitMfaAttempts(
                                                                               storage     : new InMemoryAttemptLimitStorage(),
                                                                               clock       : $limitClock,
                                                                               maxAttempts : 1,
                                                                               decaySeconds: 300
                                                                           ));
        $challengeStore->issue(record: $this->challengeRecord(expiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT5M'))));

        try {
            $flow->execute(data: new VerifyMfaChallengeData(challengeId: 'challenge-1', code: '000000'));
        } catch (MfaChallengeFailed) {
        }

        try {
            $flow->execute(data: new VerifyMfaChallengeData(challengeId: 'challenge-1', code: '000000'));
            self::fail(message: 'Expected MFA challenge lock.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(expected: MfaChallengeFailure::LOCKED, actual: $exception->reason());
        }
    }

    /**
     * @throws DateInvalidOperationException
     */
    public function testBackupCodeCanCompleteChallengeOnlyOnce() : void
    {
        [$flow, , $clock, $challengeStore, , $plainBackupCode] = $this->makeFlowWithBackupCode();
        $challengeStore->issue(record: $this->challengeRecord(expiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT5M'))));

        $result = $flow->execute(data: new VerifyMfaChallengeData(challengeId: 'challenge-1', code: $plainBackupCode));
        $this->assertTrue(condition: $result->isAuthenticated());

        $challengeStore->issue(record: $this->challengeRecord(
            expiresAt  : $clock->now()->add(interval: new DateInterval(duration: 'PT5M')),
            challengeId: 'challenge-2'
        ));

        try {
            $flow->execute(data: new VerifyMfaChallengeData(challengeId: 'challenge-2', code: $plainBackupCode));
            self::fail(message: 'Expected used backup code to fail.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(expected: MfaChallengeFailure::INVALID, actual: $exception->reason());
        }
    }

    /**
     * @return array{0: VerifyMfaChallenge, 1: Totp, 2: FrozenClock, 3: InMemoryMfaChallengeStore, 4:
     *                  CurrentAuthentication, 5: string}
     * @throws RandomException
     */
    private function makeFlowWithBackupCode() : array
    {
        [$flow, $totp, $clock, $challengeStore, $currentAuthentication] = $this->makeFlow();
        $generated        = (new GenerateBackupCodes(
            passwordHasher: new PasswordHasher(),
            clock         : $clock
        ))->execute();
        $mfaStoreProperty = new ReflectionProperty(class: VerifyMfaChallenge::class, property: 'mfaStore');
        /** @var InMemoryMfaStore $mfaStore */
        $mfaStore = $mfaStoreProperty->getValue(object: $flow);
        $method   = $mfaStore->findMethod(userId: new UserId(value: 1));
        $mfaStore->saveMethod(record: $method?->withBackupCodes(backupCodes: $generated->records) ?? throw new RuntimeException(message: 'Missing MFA method.'));

        return [$flow, $totp, $clock, $challengeStore, $currentAuthentication, $generated->backupCodeSet->values()[0]];
    }

    /**
     * @throws DateInvalidOperationException
     */
    public function testTotpCodeCannotBeReplayedWithinSameTimeStep() : void
    {
        [$flow, $totp, $clock, $challengeStore] = $this->makeFlow();
        $code = $totp->codeAt(secret: 'SECRETSECRETSECRETSECRETSECRETSE', moment: $clock->now());

        $challengeStore->issue(record: $this->challengeRecord(
            expiresAt  : $clock->now()->add(interval: new DateInterval(duration: 'PT5M')),
            challengeId: 'challenge-1'
        ));
        $result = $flow->execute(data: new VerifyMfaChallengeData(challengeId: 'challenge-1', code: $code));
        $this->assertTrue(condition: $result->isAuthenticated());

        $challengeStore->issue(record: $this->challengeRecord(
            expiresAt  : $clock->now()->add(interval: new DateInterval(duration: 'PT5M')),
            challengeId: 'challenge-2'
        ));

        try {
            $flow->execute(data: new VerifyMfaChallengeData(challengeId: 'challenge-2', code: $code));
            self::fail(message: 'Expected replayed TOTP code to fail.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(expected: MfaChallengeFailure::INVALID, actual: $exception->reason());
        }
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
