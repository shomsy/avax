<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Challenge;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Login\AuthenticationResult;
use Avax\Auth\System\Flow\Mfa\Backup\VerifyBackupCode;
use Avax\Auth\System\Flow\Mfa\MfaChallengeFailed;
use Avax\Auth\System\Flow\Mfa\MfaMethod;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Mfa\MfaVerificationAttempt;
use Avax\Auth\System\Flow\Mfa\TotpInterface;
use Avax\Auth\System\Flow\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Completes MFA verification and establishes final auth state.
 */
final readonly class VerifyMfaChallenge
{
    public function __construct(
        private MfaChallengeStoreInterface                  $challengeStore,
        private MfaStoreInterface                           $mfaStore,
        private TotpInterface                               $totp,
        #[SensitiveParameter] private VerifyBackupCode      $verifyBackupCode,
        private UserSourceInterface                         $userSource,
        private IdentityInterface                           $identity,
        private ProjectAuthenticatedUser                    $projectAuthenticatedUser,
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private AuditLogInterface                           $auditLog,
        private Clock                                       $clock,
        private LimitMfaAttempts|null                       $attemptLimit = null,
        private DeterministicRiskEngine|null                $riskEngine = null
    ) {}

    /**
     * @throws MfaChallengeFailed
     */
    public function execute(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        $record = $this->challengeStore->find(challengeId: $data->challengeId);

        if ($record === null) {
            $this->recordFailure(
                challengeId: $data->challengeId,
                reason     : 'not_found',
                ipAddress  : $data->ipAddress,
                userAgent  : $data->userAgent
            );

            throw MfaChallengeFailed::notFound();
        }

        $now = $this->clock->now();

        if ($record->isExpiredAt(moment: $now)) {
            $this->challengeStore->forget(challengeId: $record->challengeId);
            $this->recordFailure(
                challengeId: $record->challengeId,
                userId     : $record->userId->value,
                reason     : 'expired',
                ipAddress  : $data->ipAddress,
                userAgent  : $data->userAgent
            );

            throw MfaChallengeFailed::expired();
        }

        if ($record->isLocked()) {
            $this->recordFailure(
                challengeId: $record->challengeId,
                userId     : $record->userId->value,
                reason     : 'locked',
                ipAddress  : $data->ipAddress,
                userAgent  : $data->userAgent,
                suspicious : true
            );

            throw MfaChallengeFailed::locked(retryAfter: 0);
        }

        $attemptLimitKey = 'mfa:' . $record->userId->value;

        try {
            $this->attemptLimit?->check(key: $attemptLimitKey);
        } catch (MfaAttemptLimitReached $exception) {
            $this->recordFailure(
                challengeId: $record->challengeId,
                userId     : $record->userId->value,
                reason     : 'throttled',
                ipAddress  : $data->ipAddress,
                userAgent  : $data->userAgent,
                suspicious : true
            );

            throw MfaChallengeFailed::locked(retryAfter: $exception->retryAfter());
        }

        $user   = $this->userSource->findById(id: $record->userId);
        $method = $this->mfaStore->findMethod(userId: $record->userId);

        if ($user === null || ! $user->isActive() || $method === null) {
            $this->challengeStore->forget(challengeId: $record->challengeId);
            $this->recordFailure(
                challengeId: $record->challengeId,
                userId     : $record->userId->value,
                reason     : 'not_enabled',
                ipAddress  : $data->ipAddress,
                userAgent  : $data->userAgent
            );

            throw MfaChallengeFailed::notEnabled();
        }

        $acceptedMethod = null;
        $updatedMethod  = $method;
        $verification   = $this->totp->verify(
            secret              : $method->secret,
            code                : $data->code,
            moment              : $now,
            lastAcceptedTimeStep: $method->lastAcceptedTimeStep
        );

        if ($verification->accepted && $verification->timeStep !== null) {
            $acceptedMethod = MfaMethod::TOTP;
            $updatedMethod  = $method->withLastAcceptedTimeStep(timeStep: $verification->timeStep);
            $this->mfaStore->saveMethod(record: $updatedMethod);
        } elseif ($this->verifyBackupCode->execute(userId: $record->userId, code: $data->code)) {
            $acceptedMethod = MfaMethod::BACKUP_CODE;
        }

        if ($acceptedMethod === null) {
            $this->attemptLimit?->recordFailed(key: $attemptLimitKey);
            $updatedRecord = $record->recordAttempt(attempt: new MfaVerificationAttempt(
                                                        occurredAt: $now,
                                                        accepted  : false,
                                                        reason    : $verification->reason
                                                    ));
            $this->challengeStore->save(record: $updatedRecord);
            $this->recordFailure(
                challengeId: $record->challengeId,
                userId     : $record->userId->value,
                reason     : $verification->reason,
                ipAddress  : $data->ipAddress,
                userAgent  : $data->userAgent,
                suspicious : $updatedRecord->isLocked()
            );

            throw MfaChallengeFailed::invalidCode();
        }

        $this->challengeStore->forget(challengeId: $record->challengeId);
        $this->attemptLimit?->reset(key: $attemptLimitKey);
        $issued  = $this->identity->issue(user: $user, mfaVerifiedAt: $now);
        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $data->ipAddress,
            userAgent: $data->userAgent
        );
        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : $issued->mode,
            sessionId           : $issued->sessionId,
            accessTokenId       : $issued->accessToken?->tokenId,
            accessTokenExpiresAt: $issued->accessToken?->expiresAt,
            refreshTokenId      : $issued->refreshToken?->tokenId,
            mfaVerifiedAt       : $now,
            phishingResistant   : $issued->phishingResistant
        );

        $this->currentAuthentication->store(context: $context);
        $riskDecision = $this->riskEngine?->assessSuccessfulAuthentication(
            user      : $user,
            ipAddress : $data->ipAddress,
            userAgent : $data->userAgent
        );
        $this->auditLog->record(event: new AuditEvent(
                                    name      : 'auth.mfa.challenge.passed',
                                    occurredAt: $now,
                                    context   : [
                                                    'user_id'      => $user->getId()->value,
                                                    'challenge_id' => $record->challengeId,
                                                    'purpose'      => $record->purpose->value,
                                                    'method'       => $acceptedMethod->value,
                                                    'risk_action'  => $riskDecision?->action->value,
                                                    'ip_address'   => $data->ipAddress,
                                                    'user_agent'   => $data->userAgent,
                                                ]
                                ));

        return AuthenticationResult::success(
            context     : $context,
            accessToken : $issued->accessToken?->token,
            refreshToken: $issued->refreshToken?->token
        );
    }

    private function recordFailure(
        string                            $challengeId,
        string                            $reason,
        #[SensitiveParameter] string|null $ipAddress,
        string|null                       $userAgent,
        int|null                          $userId = null,
        bool                              $suspicious = false
    ) : void
    {
        $this->auditLog->record(event: new AuditEvent(
                                    name      : 'auth.mfa.challenge.failed',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id'      => $userId,
                                                    'challenge_id' => $challengeId,
                                                    'reason'       => $reason,
                                                    'ip_address'   => $ipAddress,
                                                    'user_agent'   => $userAgent,
                                                ]
                                ));

        if (! $suspicious) {
            return;
        }

        $this->auditLog->record(event: new AuditEvent(
                                    name      : 'auth.mfa.suspicious_failures',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id'      => $userId,
                                                    'challenge_id' => $challengeId,
                                                    'reason'       => $reason,
                                                    'ip_address'   => $ipAddress,
                                                    'user_agent'   => $userAgent,
                                                ]
                                ));
    }
}
