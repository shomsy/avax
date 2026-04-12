<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Mfa\Challenge;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Identity\IssuedAuthentication;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Mfa\Backup\GenerateBackupCodes;
use Avax\Auth\System\Flow\Mfa\Backup\VerifyBackupCode;
use Avax\Auth\System\Flow\Mfa\Challenge\InMemoryAttemptLimitStorage;
use Avax\Auth\System\Flow\Mfa\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Flow\Mfa\Challenge\LimitMfaAttempts;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeRecord;
use Avax\Auth\System\Flow\Mfa\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Flow\Mfa\InMemoryMfaStore;
use Avax\Auth\System\Flow\Mfa\MfaChallengeFailed;
use Avax\Auth\System\Flow\Mfa\MfaChallengeFailure;
use Avax\Auth\System\Flow\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Flow\Mfa\MfaMethod;
use Avax\Auth\System\Flow\Mfa\MfaMethodRecord;
use Avax\Auth\System\Flow\Mfa\Totp;
use Avax\Auth\System\Flow\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Flow\Token\IssuedRefreshToken;
use Avax\Auth\System\Flow\Token\IssuedToken;
use Avax\Auth\System\Flow\Verify\InMemoryEmailVerificationStateStore;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MFA challenge verification.
 */
final class VerifyMfaChallengeTest extends TestCase
{
    public function testValidTotpCompletesAuthentication() : void
    {
        [$flow, $totp, $clock, $challengeStore, $currentAuthentication] = $this->makeFlow();
        $challengeStore->issue($this->challengeRecord(expiresAt: $clock->now()->add(new DateInterval('PT5M'))));

        $result = $flow->execute(new VerifyMfaChallengeData(
                                     challengeId: 'challenge-1',
                                     code       : $totp->codeAt('SECRETSECRETSECRETSECRETSECRETSE', $clock->now())
                                 ));

        $this->assertTrue($result->isAuthenticated());
        $this->assertNotNull($result->context()->mfaVerifiedAt());
        $this->assertTrue($currentAuthentication->read()->isAuthenticated());
        $this->assertNull($challengeStore->find('challenge-1'));
    }

    /**
     * @return array{0: VerifyMfaChallenge, 1: Totp, 2: FrozenClock, 3: InMemoryMfaChallengeStore, 4:
     *                  CurrentAuthentication}
     */
    private function makeFlow(LimitMfaAttempts|null $attemptLimit = null) : array
    {
        $clock      = new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'));
        $userSource = new InMemoryUserSource();
        $user       = User::create(
            id          : new UserId(1),
            email       : new UserEmail('user@example.com'),
            username    : 'user',
            passwordHash: 'hash'
        );
        $userSource->create($user);

        $mfaStore = new InMemoryMfaStore();
        $mfaStore->saveMethod(new MfaMethodRecord(
                                  userId   : new UserId(1),
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
                                       expiresAt: $verifiedAt->modify('+1 hour')
                                   ),
                    refreshToken : new IssuedRefreshToken(
                                       token        : 'refresh-token',
                                       tokenId      : 'refresh-id',
                                       familyId     : 'family-id',
                                       userId       : $issuedUser->getId(),
                                       expiresAt    : $verifiedAt->modify('+30 days'),
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

    private function challengeRecord(DateTimeImmutable $expiresAt, string $challengeId = 'challenge-1') : MfaChallengeRecord
    {
        return new MfaChallengeRecord(
            challengeId: $challengeId,
            userId     : new UserId(1),
            purpose    : MfaChallengePurpose::LOGIN,
            createdAt  : $expiresAt->sub(new DateInterval('PT1M')),
            expiresAt  : $expiresAt
        );
    }

    public function testInvalidTotpFails() : void
    {
        [$flow, , $clock, $challengeStore] = $this->makeFlow();
        $challengeStore->issue($this->challengeRecord(expiresAt: $clock->now()->add(new DateInterval('PT5M'))));

        try {
            $flow->execute(new VerifyMfaChallengeData('challenge-1', '000000'));
            self::fail('Expected MFA challenge failure.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(MfaChallengeFailure::INVALID, $exception->reason());
        }
    }

    public function testExpiredChallengeFails() : void
    {
        [$flow, $totp, $clock, $challengeStore] = $this->makeFlow();
        $challengeStore->issue($this->challengeRecord(expiresAt: $clock->now()->sub(new DateInterval('PT1M'))));

        try {
            $flow->execute(new VerifyMfaChallengeData(
                               challengeId: 'challenge-1',
                               code       : $totp->codeAt('SECRETSECRETSECRETSECRETSECRETSE', $clock->now())
                           ));
            self::fail('Expected MFA challenge failure.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(MfaChallengeFailure::EXPIRED, $exception->reason());
        }
    }

    public function testRepeatedFailuresBecomeLocked() : void
    {
        $limitClock = new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'));
        [$flow, , $clock, $challengeStore] = $this->makeFlow(attemptLimit: new LimitMfaAttempts(
                                                                               storage     : new InMemoryAttemptLimitStorage(),
                                                                               clock       : $limitClock,
                                                                               maxAttempts : 1,
                                                                               decaySeconds: 300
                                                                           ));
        $challengeStore->issue($this->challengeRecord(expiresAt: $clock->now()->add(new DateInterval('PT5M'))));

        try {
            $flow->execute(new VerifyMfaChallengeData('challenge-1', '000000'));
        } catch (MfaChallengeFailed) {
        }

        try {
            $flow->execute(new VerifyMfaChallengeData('challenge-1', '000000'));
            self::fail('Expected MFA challenge lock.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(MfaChallengeFailure::LOCKED, $exception->reason());
        }
    }

    public function testBackupCodeCanCompleteChallengeOnlyOnce() : void
    {
        [$flow, , $clock, $challengeStore, , $plainBackupCode] = $this->makeFlowWithBackupCode();
        $challengeStore->issue($this->challengeRecord(expiresAt: $clock->now()->add(new DateInterval('PT5M'))));

        $result = $flow->execute(new VerifyMfaChallengeData('challenge-1', $plainBackupCode));
        $this->assertTrue($result->isAuthenticated());

        $challengeStore->issue($this->challengeRecord(
            challengeId: 'challenge-2',
            expiresAt  : $clock->now()->add(new DateInterval('PT5M'))
        ));

        try {
            $flow->execute(new VerifyMfaChallengeData('challenge-2', $plainBackupCode));
            self::fail('Expected used backup code to fail.');
        } catch (MfaChallengeFailed $exception) {
            $this->assertSame(MfaChallengeFailure::INVALID, $exception->reason());
        }
    }

    /**
     * @return array{0: VerifyMfaChallenge, 1: Totp, 2: FrozenClock, 3: InMemoryMfaChallengeStore, 4:
     *                  CurrentAuthentication, 5: string}
     */
    private function makeFlowWithBackupCode() : array
    {
        [$flow, $totp, $clock, $challengeStore, $currentAuthentication] = $this->makeFlow();
        $generated        = (new GenerateBackupCodes(
            passwordHasher: new PasswordHasher(),
            clock         : $clock
        ))->execute();
        $mfaStoreProperty = new \ReflectionProperty(VerifyMfaChallenge::class, 'mfaStore');
        $mfaStoreProperty->setAccessible(true);
        /** @var InMemoryMfaStore $mfaStore */
        $mfaStore = $mfaStoreProperty->getValue($flow);
        $method   = $mfaStore->findMethod(new UserId(1));
        $mfaStore->saveMethod($method?->withBackupCodes($generated->records) ?? throw new \RuntimeException('Missing MFA method.'));

        return [$flow, $totp, $clock, $challengeStore, $currentAuthentication, $generated->backupCodeSet->values()[0]];
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
