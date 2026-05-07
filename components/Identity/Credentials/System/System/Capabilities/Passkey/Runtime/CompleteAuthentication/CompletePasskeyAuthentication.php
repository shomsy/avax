<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\Runtime\CompleteAuthentication;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeRecord;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredential;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\Runtime\PasskeyOperationFailed;
use SensitiveParameter;

final readonly class CompletePasskeyAuthentication
{
    public function __construct(
        private PasskeyRuntimeInterface         $passkeyRuntime,
        private PasskeyChallengeStoreInterface  $passkeyChallengeStore,
        #[SensitiveParameter]
        private PasskeyCredentialStoreInterface $passkeyCredentialStore,
        private UserSourceInterface             $userSource,
        private IdentityInterface               $identity,
        private ProjectAuthenticatedUser        $projectAuthenticatedUser,
        #[SensitiveParameter]
        private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private string                          $rpId,
    ) {}

    /**
     * @throws PasskeyOperationFailed
     */
    public function execute(CompletePasskeyAuthenticationData $completePasskeyAuthenticationData) : AuthenticationResult
    {
        $challenge = $this->passkeyChallengeStore->find(challengeId: $completePasskeyAuthenticationData->challengeId);

        if (! $challenge instanceof PasskeyChallengeRecord) {
            throw PasskeyOperationFailed::notFound();
        }

        if ($challenge->wasUsed()) {
            throw PasskeyOperationFailed::alreadyUsed();
        }

        if ($challenge->isExpiredAt(moment: $this->clock->now())) {
            $this->passkeyChallengeStore->forget(challengeId: $completePasskeyAuthenticationData->challengeId);

            throw PasskeyOperationFailed::expired();
        }

        $knownCredentials              = $challenge->userId !== null
            ? $this->passkeyCredentialStore->forUser(userId: $challenge->userId)
            : [];
        $verifiedPasskeyAuthentication = $this->passkeyRuntime->completeAuthentication(
            rpId            : $this->rpId,
            challenge       : $challenge->challenge,
            response        : $completePasskeyAuthenticationData->response,
            knownCredentials: $knownCredentials,
        );
        $credential                    = $this->passkeyCredentialStore->find(credentialId: $verifiedPasskeyAuthentication->credentialId);

        if (! $credential instanceof PasskeyCredential || $credential->isRevoked()) {
            throw PasskeyOperationFailed::notFound();
        }

        $user = $this->userSource->findById(id: new UserId(value: $verifiedPasskeyAuthentication->userId));

        if (! $user instanceof User || ! $user->isActive()) {
            throw PasskeyOperationFailed::notFound();
        }

        $issuedAuthentication  = $this->identity->issue(
            user             : $user,
            mfaVerifiedAt    : $this->clock->now(),
            phishingResistant: true,
        );
        $authenticationContext = AuthenticationContext::authenticated(
            sessionId           : $issuedAuthentication->sessionId,
            accessTokenId       : $issuedAuthentication->accessToken?->tokenId,
            accessTokenExpiresAt: $issuedAuthentication->accessToken?->expiresAt,
            refreshTokenId      : $issuedAuthentication->refreshToken?->tokenId,
            mfaVerifiedAt       : $this->clock->now(),
            phishingResistant   : true,
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : $issuedAuthentication->mode,
        );

        $this->currentAuthentication->store(context: $authenticationContext);
        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $completePasskeyAuthenticationData->ipAddress,
            userAgent: $completePasskeyAuthenticationData->userAgent,
        );
        $this->passkeyCredentialStore->touch(credentialId: $credential->credentialId, usedAt: $this->clock->now());
        $this->passkeyChallengeStore->markUsed(challengeId: $completePasskeyAuthenticationData->challengeId, usedAt: $this->clock->now());
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.passkey.authentication.succeeded',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'       => $user->getId()->value,
                                                           'credential_id' => $credential->credentialId,
                                                           'ip_address'    => $completePasskeyAuthenticationData->ipAddress,
                                                           'user_agent'    => $completePasskeyAuthenticationData->userAgent,
                                                       ],
                                       ));

        return AuthenticationResult::success(
            accessToken : $issuedAuthentication->accessToken?->token,
            refreshToken: $issuedAuthentication->refreshToken?->token,
            context     : $authenticationContext,
        );
    }
}
