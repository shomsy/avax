<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\BackChannelLogout;

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

final readonly class BackChannelLogout
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication         $currentAuthentication,
        private IdentityInterface             $identity,
        private AuditLogInterface             $auditLog,
        private Clock                         $clock,
        #[SensitiveParameter]
        private SessionRegistryInterface|null     $sessionRegistry = null,
        #[SensitiveParameter]
        private RefreshTokenStoreInterface|null   $refreshTokenStore = null,
        private OidcProviderInterface|null        $oidcProvider = null,
        private OAuthClientRegistryInterface|null $oAuthClientRegistry = null,
    ) {}

    public function execute(BackChannelLogoutData $backChannelLogoutData) : LogoutResult
    {
        $authenticationContext = $this->currentAuthentication->read();
        $now                   = $this->clock->now();
        $claims                = $this->oidcProvider?->resolveJwt(jwt: $backChannelLogoutData->logoutToken);
        $sessionId             = is_string(value: $claims['sid'] ?? null) ? trim(string: $claims['sid']) : null;
        $events                = $claims['events'] ?? null;
        $backChannelEvent      = 'https://schemas.openid.net/event/backchannel-logout';
        $client                = $this->resolveClientFromClaims(claims: $claims);

        if (! is_array(value: $events) || ! array_key_exists(key: $backChannelEvent, array: $events)) {
            return new LogoutResult(revoked: false);
        }

        if ($sessionId !== null && $sessionId !== '') {
            $this->sessionRegistry?->revoke(sessionId: $sessionId, revokedAt: $now, reason: 'oidc_back_channel_logout');
        }

        if ($authenticationContext->refreshTokenFamilyId() !== null) {
            $this->refreshTokenStore?->revokeFamily(familyId: $authenticationContext->refreshTokenFamilyId());
        }

        $user = $authenticationContext->user();

        if ($user instanceof AuthenticatedUser) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.oidc.back_channel_logout.succeeded',
                                               occurredAt: $now,
                                               context   : [
                                                               'user_id'                        => $user->id,
                                                               'session_id'                     => $sessionId,
                                                               'aud'                            => $claims['aud'] ?? null,
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
            revoked  : $sessionId !== null,
            sessionId: $sessionId,
        );
    }

    /**
     * @param array<string, mixed>|null $claims
     */
    private function resolveClientFromClaims(array|null $claims) : ?OAuthClient
    {
        if ($claims === null || ! $this->oAuthClientRegistry instanceof OAuthClientRegistryInterface) {
            return null;
        }

        $audience = $claims['aud'] ?? null;
        $clientId = is_string(value: $audience) ? trim(string: $audience) : null;

        if ($clientId === null || $clientId === '') {
            return null;
        }

        return $this->oAuthClientRegistry->find(clientId: $clientId);
    }
}
