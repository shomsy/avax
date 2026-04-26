<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyOperationFailed;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyRuntimeInterface;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class CompletePasskeyAuthentication
{
    public function __construct(
        private PasskeyRuntimeInterface                               $runtime,
        private PasskeyChallengeStoreInterface                        $challengeStore,
        #[SensitiveParameter] private PasskeyCredentialStoreInterface $credentialStore,
        private UserSourceInterface                                   $userSource,
        private IdentityInterface                                     $identity,
        private ProjectAuthenticatedUser                              $projectAuthenticatedUser,
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        private string                                                $rpId
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        $challenge = $this->challengeStore->find(challengeId: $data->challengeId);

        if ($challenge === null) {
            throw PasskeyOperationFailed::notFound();
        }

        if ($challenge->wasUsed()) {
            throw PasskeyOperationFailed::alreadyUsed();
        }

        if ($challenge->isExpiredAt(moment: $this->clock->now())) {
            $this->challengeStore->forget(challengeId: $data->challengeId);
            throw PasskeyOperationFailed::expired();
        }

        $knownCredentials = $challenge->userId !== null
            ? $this->credentialStore->forUser(userId: $challenge->userId)
            : [];
        $verified         = $this->runtime->completeAuthentication(
            rpId            : $this->rpId,
            challenge       : $challenge->challenge,
            response        : $data->response,
            knownCredentials: $knownCredentials
        );
        $credential       = $this->credentialStore->find(credentialId: $verified->credentialId);

        if ($credential === null || $credential->isRevoked()) {
            throw PasskeyOperationFailed::notFound();
        }

        $user = $this->userSource->findById(id: new UserId(value: $verified->userId));

        if ($user === null || ! $user->isActive()) {
            throw PasskeyOperationFailed::notFound();
        }

        $issued  = $this->identity->issue(
            user             : $user,
            mfaVerifiedAt    : $this->clock->now(),
            phishingResistant: true
        );
        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : $issued->mode,
            sessionId           : $issued->sessionId,
            accessTokenId       : $issued->accessToken?->tokenId,
            accessTokenExpiresAt: $issued->accessToken?->expiresAt,
            refreshTokenId      : $issued->refreshToken?->tokenId,
            mfaVerifiedAt       : $this->clock->now(),
            phishingResistant   : true
        );

        $this->currentAuthentication->store(context: $context);
        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $data->ipAddress,
            userAgent: $data->userAgent
        );
        $this->credentialStore->touch(credentialId: $credential->credentialId, usedAt: $this->clock->now());
        $this->challengeStore->markUsed(challengeId: $data->challengeId, usedAt: $this->clock->now());
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.authentication.succeeded',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'       => $user->getId()->value,
                                                           'credential_id' => $credential->credentialId,
                                                           'ip_address'    => $data->ipAddress,
                                                           'user_agent'    => $data->userAgent,
                                                       ]
                                       ));

        return AuthenticationResult::success(
            context     : $context,
            accessToken : $issued->accessToken?->token,
            refreshToken: $issued->refreshToken?->token
        );
    }
}
