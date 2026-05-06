<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\VerifyBackupCode;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Data\VerifyMfaChallengeData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaMethod;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\MfaAttemptLimitReached;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\MfaChallengeFailed;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaMethodRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaVerificationAttempt;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\TotpInterface;
use SensitiveParameter;

/**
 * Completes MFA verification and establishes final auth state.
 */
final readonly class VerifyMfaChallenge
{
    public function __construct(
        private MfaChallengeStoreInterface $mfaChallengeStore,
        private MfaStoreInterface $mfaStore,
        private TotpInterface $totp,
        #[SensitiveParameter]
        private VerifyBackupCode $verifyBackupCode,
        private UserSourceInterface $userSource,
        private IdentityInterface $identity,
        private ProjectAuthenticatedUser $projectAuthenticatedUser,
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private ?LimitMfaAttempts $limitMfaAttempts = null,
        private ?DeterministicRiskEngine $deterministicRiskEngine = null,
    ) {
    }

    /**
     * @throws MfaChallengeFailed
     */
    public function execute(VerifyMfaChallengeData $verifyMfaChallengeData): AuthenticationResult
    {
        $record = $this->mfaChallengeStore->find(challengeId: $verifyMfaChallengeData->challengeId);

        if (! $record instanceof MfaChallengeRecord) {
            $this->recordFailure(
                challengeId: $verifyMfaChallengeData->challengeId,
                reason     : 'not_found',
                ipAddress  : $verifyMfaChallengeData->ipAddress,
                userAgent  : $verifyMfaChallengeData->userAgent,
            );

            throw MfaChallengeFailed::notFound();
        }

        $now = $this->clock->now();

        if ($record->isExpiredAt(moment: $now)) {
            $this->mfaChallengeStore->forget(challengeId: $record->challengeId);
            $this->recordFailure(
                challengeId: $record->challengeId,
                reason     : 'expired',
                ipAddress  : $verifyMfaChallengeData->ipAddress,
                userAgent  : $verifyMfaChallengeData->userAgent,
                userId     : $record->userId->value,
            );

            throw MfaChallengeFailed::expired();
        }

        if ($record->isLocked()) {
            $this->recordFailure(
                challengeId: $record->challengeId,
                reason     : 'locked',
                ipAddress  : $verifyMfaChallengeData->ipAddress,
                userAgent  : $verifyMfaChallengeData->userAgent,
                userId     : $record->userId->value,
                suspicious : true,
            );

            throw MfaChallengeFailed::locked(retryAfter: 0);
        }

        $attemptLimitKey = 'mfa:'.$record->userId->value;

        try {
            $this->limitMfaAttempts?->check(key: $attemptLimitKey);
        } catch (MfaAttemptLimitReached $mfaAttemptLimitReached) {
            $this->recordFailure(
                challengeId: $record->challengeId,
                reason     : 'throttled',
                ipAddress  : $verifyMfaChallengeData->ipAddress,
                userAgent  : $verifyMfaChallengeData->userAgent,
                userId     : $record->userId->value,
                suspicious : true,
            );

            throw MfaChallengeFailed::locked(retryAfter: $mfaAttemptLimitReached->retryAfter());
        }

        $user = $this->userSource->findById(id: $record->userId);
        $method = $this->mfaStore->findMethod(userId: $record->userId);

        if (! $user instanceof User || ! $user->isActive() || ! $method instanceof MfaMethodRecord) {
            $this->mfaChallengeStore->forget(challengeId: $record->challengeId);
            $this->recordFailure(
                challengeId: $record->challengeId,
                reason     : 'not_enabled',
                ipAddress  : $verifyMfaChallengeData->ipAddress,
                userAgent  : $verifyMfaChallengeData->userAgent,
                userId     : $record->userId->value,
            );

            throw MfaChallengeFailed::notEnabled();
        }

        $acceptedMethod = null;
        $updatedMethod = $method;
        $totpVerification = $this->totp->verify(
            secret              : $method->secret,
            code                : $verifyMfaChallengeData->code,
            moment              : $now,
            lastAcceptedTimeStep: $method->lastAcceptedTimeStep,
        );

        if ($totpVerification->accepted && $totpVerification->timeStep !== null) {
            $acceptedMethod = MfaMethod::TOTP;
            $updatedMethod = $method->withLastAcceptedTimeStep(timeStep: $totpVerification->timeStep);
            $this->mfaStore->saveMethod(record: $updatedMethod);
        } elseif ($this->verifyBackupCode->execute(userId: $record->userId, code: $verifyMfaChallengeData->code)) {
            $acceptedMethod = MfaMethod::BACKUP_CODE;
        }

        if ($acceptedMethod === null) {
            $this->limitMfaAttempts?->recordFailed(key: $attemptLimitKey);
            $updatedRecord = $record->recordAttempt(attempt: new MfaVerificationAttempt(
                occurredAt: $now,
                accepted  : false,
                reason    : $totpVerification->reason,
            ));
            $this->mfaChallengeStore->save(record: $updatedRecord);
            $this->recordFailure(
                challengeId: $record->challengeId,
                reason     : $totpVerification->reason,
                ipAddress  : $verifyMfaChallengeData->ipAddress,
                userAgent  : $verifyMfaChallengeData->userAgent,
                userId     : $record->userId->value,
                suspicious : $updatedRecord->isLocked(),
            );

            throw MfaChallengeFailed::invalidCode();
        }

        $this->mfaChallengeStore->forget(challengeId: $record->challengeId);
        $this->limitMfaAttempts?->reset(key: $attemptLimitKey);
        $issuedAuthentication = $this->identity->issue(user: $user, mfaVerifiedAt: $now);
        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $verifyMfaChallengeData->ipAddress,
            userAgent: $verifyMfaChallengeData->userAgent,
        );
        $authenticationContext = AuthenticationContext::authenticated(
            sessionId           : $issuedAuthentication->sessionId,
            accessTokenId       : $issuedAuthentication->accessToken?->tokenId,
            accessTokenExpiresAt: $issuedAuthentication->accessToken?->expiresAt,
            refreshTokenId      : $issuedAuthentication->refreshToken?->tokenId,
            mfaVerifiedAt       : $now,
            phishingResistant   : $issuedAuthentication->phishingResistant,
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : $issuedAuthentication->mode,
        );

        $this->currentAuthentication->store(context: $authenticationContext);
        $riskDecision = $this->deterministicRiskEngine?->assessSuccessfulAuthentication(
            user     : $user,
            ipAddress: $verifyMfaChallengeData->ipAddress,
            userAgent: $verifyMfaChallengeData->userAgent,
        );
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.challenge.passed',
            occurredAt: $now,
            context   : [
                'user_id' => $user->getId()->value,
                'challenge_id' => $record->challengeId,
                'purpose' => $record->purpose->value,
                'method' => $acceptedMethod->value,
                'risk_action' => $riskDecision?->action->value,
                'ip_address' => $verifyMfaChallengeData->ipAddress,
                'user_agent' => $verifyMfaChallengeData->userAgent,
            ],
        ));

        return AuthenticationResult::success(
            accessToken : $issuedAuthentication->accessToken?->token,
            refreshToken: $issuedAuthentication->refreshToken?->token,
            context     : $authenticationContext,
        );
    }

    private function recordFailure(
        string $challengeId,
        string $reason,
        #[SensitiveParameter]
        ?string $ipAddress,
        ?string $userAgent,
        ?int $userId = null,
        bool $suspicious = false,
    ): void {
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.challenge.failed',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $userId,
                'challenge_id' => $challengeId,
                'reason' => $reason,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ],
        ));

        if (! $suspicious) {
            return;
        }

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.suspicious_failures',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $userId,
                'challenge_id' => $challengeId,
                'reason' => $reason,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ],
        ));
    }
}
