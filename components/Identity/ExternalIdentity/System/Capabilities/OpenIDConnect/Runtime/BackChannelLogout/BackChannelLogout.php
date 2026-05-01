<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\BackChannelLogout;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Support\OidcProviderInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class BackChannelLogout
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private IdentityInterface $identity,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        #[SensitiveParameter]
        private SessionRegistryInterface|null $sessionRegistry = null,
        #[SensitiveParameter]
        private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private OidcProviderInterface|null $oidcProvider = null,
        private OAuthClientRegistryInterface|null $clientRegistry = null,
    ) {
    }

    public function execute(BackChannelLogoutData $data) : LogoutResult
    {
        $context          = $this->currentAuthentication->read();
        $now              = $this->clock->now();
        $claims           = $this->oidcProvider?->resolveJwt(jwt: $data->logoutToken);
        $sessionId        = is_string(value: $claims['sid'] ?? null) ? trim(string: $claims['sid']) : null;
        $events           = $claims['events'] ?? null;
        $backChannelEvent = 'https://schemas.openid.net/event/backchannel-logout';
        $client           = $this->resolveClientFromClaims(claims: $claims);

        if (! is_array(value: $events) || ! array_key_exists(key: $backChannelEvent, array: $events)) {
            return new LogoutResult(revoked: false);
        }

        if ($sessionId !== null && $sessionId !== '') {
            $this->sessionRegistry?->revoke(sessionId: $sessionId, revokedAt: $now, reason: 'oidc_back_channel_logout');
        }

        if ($context->refreshTokenFamilyId() !== null) {
            $this->refreshTokenStore?->revokeFamily(familyId: $context->refreshTokenFamilyId());
        }

        $user = $context->user();

        if ($user !== null) {
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

        if ($user !== null) {
            $this->identity->clear(context: $context);
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
    private function resolveClientFromClaims(array|null $claims) : OAuthClient|null
    {
        if ($claims === null || $this->clientRegistry === null) {
            return null;
        }

        $audience = $claims['aud'] ?? null;
        $clientId = is_string(value: $audience) ? trim(string: $audience) : null;

        if ($clientId === null || $clientId === '') {
            return null;
        }

        return $this->clientRegistry->find(clientId: $clientId);
    }
}
