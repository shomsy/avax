<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\FrontChannelLogout;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use SensitiveParameter;

final readonly class FrontChannelLogout
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication         $currentAuthentication,
        private IdentityInterface             $identity,
        private AuditLogInterface             $auditLog,
        private Clock                         $clock,
        #[SensitiveParameter]
        private ?SessionRegistryInterface     $sessionRegistry = null,
        #[SensitiveParameter]
        private ?RefreshTokenStoreInterface   $refreshTokenStore = null,
        private ?OidcProviderInterface        $oidcProvider = null,
        private ?OAuthClientRegistryInterface $oAuthClientRegistry = null,
    ) {}

    public function execute(FrontChannelLogoutData $frontChannelLogoutData) : LogoutResult
    {
        $authenticationContext = $this->currentAuthentication->read();
        $now                   = $this->clock->now();
        $sessionId             = $frontChannelLogoutData->sessionId ?? $authenticationContext->sessionId();

        if ($sessionId === null && $frontChannelLogoutData->idTokenHint !== null && $this->oidcProvider instanceof OidcProviderInterface) {
            $claims    = $this->oidcProvider->resolveIdToken(idToken: $frontChannelLogoutData->idTokenHint);
            $sessionId = is_string(value: $claims['sid'] ?? null) ? trim(string: $claims['sid']) : null;
        }

        if ($sessionId !== null && $sessionId !== '') {
            $this->sessionRegistry?->revoke(sessionId: $sessionId, revokedAt: $now, reason: 'oidc_front_channel_logout');
        }

        $client = $this->resolveClientFromIdTokenHint(idTokenHint: $frontChannelLogoutData->idTokenHint);

        if ($authenticationContext->refreshTokenFamilyId() !== null) {
            $this->refreshTokenStore?->revokeFamily(familyId: $authenticationContext->refreshTokenFamilyId());
        }

        $user = $authenticationContext->user();

        if ($user instanceof AuthenticatedUser) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.oidc.front_channel_logout.succeeded',
                                               occurredAt: $now,
                                               context   : [
                                                               'user_id'                        => $user->id,
                                                               'session_id'                     => $sessionId,
                                                               'state'                          => $frontChannelLogoutData->state,
                                                               'client_id'                      => $client?->clientId,
                                                               'front_channel_logout_supported' => $client?->frontChannelLogoutSupported,
                                                               'back_channel_logout_supported'  => $client?->backChannelLogoutSupported,
                                                           ],
                                           ));
        }

        if ($user instanceof AuthenticatedUser) {
            $this->identity->clear(context: $authenticationContext);
        }

        $this->currentAuthentication->clear();

        return new LogoutResult(
            revoked              : $sessionId !== null,
            sessionId            : $sessionId,
            postLogoutRedirectUri: $frontChannelLogoutData->postLogoutRedirectUri,
            state                : $frontChannelLogoutData->state,
        );
    }

    private function resolveClientFromIdTokenHint(#[SensitiveParameter] ?string $idTokenHint) : ?OAuthClient
    {
        if ($idTokenHint === null || trim(string: $idTokenHint) === '' || ! $this->oidcProvider instanceof OidcProviderInterface || ! $this->oAuthClientRegistry instanceof OAuthClientRegistryInterface) {
            return null;
        }

        $claims = $this->oidcProvider->resolveIdToken(idToken: $idTokenHint);

        if (! is_string(value: $claims['aud'] ?? null) || trim(string: $claims['aud']) === '') {
            return null;
        }

        return $this->oAuthClientRegistry->find(clientId: trim(string: $claims['aud']));
    }
}
