<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Passkey\CompleteAuthentication;

use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Passkey\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capabilities\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capabilities\Passkey\PasskeyRuntimeInterface;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Passkey\PasskeyOperationFailed;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class CompletePasskeyAuthentication
{
    private string                          $rpId;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private CurrentAuthentication           $currentAuthentication;
    private ProjectAuthenticatedUser        $projectAuthenticatedUser;
    private IdentityInterface               $identity;
    private UserSourceInterface             $userSource;
    private PasskeyCredentialStoreInterface $credentialStore;
    private PasskeyChallengeStoreInterface  $challengeStore;
    private PasskeyRuntimeInterface         $runtime;

    public function __construct(
        PasskeyRuntimeInterface                               $runtime,
        PasskeyChallengeStoreInterface                        $challengeStore,
        #[SensitiveParameter] PasskeyCredentialStoreInterface $credentialStore,
        UserSourceInterface                                   $userSource,
        IdentityInterface                                     $identity,
        ProjectAuthenticatedUser                              $projectAuthenticatedUser,
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        string                                                $rpId
    )
    {
        $this->runtime                  = $runtime;
        $this->challengeStore           = $challengeStore;
        $this->credentialStore          = $credentialStore;
        $this->userSource               = $userSource;
        $this->identity                 = $identity;
        $this->projectAuthenticatedUser = $projectAuthenticatedUser;
        $this->currentAuthentication    = $currentAuthentication;
        $this->auditLog                 = $auditLog;
        $this->clock                    = $clock;
        $this->rpId                     = $rpId;
    }

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
