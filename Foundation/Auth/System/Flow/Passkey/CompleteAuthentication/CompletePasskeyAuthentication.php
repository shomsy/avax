<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\CompleteAuthentication;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyRuntimeInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Login\AuthenticationResult;
use Avax\Auth\System\Flow\Passkey\PasskeyOperationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class CompletePasskeyAuthentication
{
    public function __construct(
        private PasskeyRuntimeInterface $runtime,
        private PasskeyChallengeStoreInterface $challengeStore,
        private PasskeyCredentialStoreInterface $credentialStore,
        private UserSourceInterface $userSource,
        private IdentityInterface $identity,
        private ProjectAuthenticatedUser $projectAuthenticatedUser,
        private CurrentAuthentication $currentAuthentication,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private string $rpId
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        $challenge = $this->challengeStore->find($data->challengeId);

        if ($challenge === null) {
            throw PasskeyOperationFailed::notFound();
        }

        if ($challenge->wasUsed()) {
            throw PasskeyOperationFailed::alreadyUsed();
        }

        if ($challenge->isExpiredAt($this->clock->now())) {
            $this->challengeStore->forget($data->challengeId);
            throw PasskeyOperationFailed::expired();
        }

        $knownCredentials = $challenge->userId !== null
            ? $this->credentialStore->forUser($challenge->userId)
            : [];
        $verified         = $this->runtime->completeAuthentication(
            rpId            : $this->rpId,
            challenge       : $challenge->challenge,
            response        : $data->response,
            knownCredentials: $knownCredentials
        );
        $credential       = $this->credentialStore->find($verified->credentialId);

        if ($credential === null || $credential->isRevoked()) {
            throw PasskeyOperationFailed::notFound();
        }

        $user = $this->userSource->findById(new UserId($verified->userId));

        if ($user === null || ! $user->isActive()) {
            throw PasskeyOperationFailed::notFound();
        }

        $issued  = $this->identity->issue(
            $user,
            $this->clock->now(),
            true
        );
        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser($user),
            mode                : $issued->mode === AuthenticationMode::SESSION ? AuthenticationMode::SESSION : $issued->mode,
            sessionId           : $issued->sessionId,
            accessTokenId       : $issued->accessToken?->tokenId,
            accessTokenExpiresAt: $issued->accessToken?->expiresAt,
            refreshTokenId      : $issued->refreshToken?->tokenId,
            mfaVerifiedAt       : $this->clock->now(),
            phishingResistant   : true
        );

        $this->currentAuthentication->store($context);
        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $data->ipAddress,
            userAgent: $data->userAgent
        );
        $this->credentialStore->touch($credential->credentialId, $this->clock->now());
        $this->challengeStore->markUsed($data->challengeId, $this->clock->now());
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.passkey.authentication.succeeded',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->getId()->value,
                'credential_id' => $credential->credentialId,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));

        return AuthenticationResult::success(
            context     : $context,
            accessToken : $issued->accessToken?->token,
            refreshToken: $issued->refreshToken?->token
        );
    }
}
