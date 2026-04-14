<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\BackChannelLogout;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Oidc\Logout\LogoutResult;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class BackChannelLogout
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private IdentityInterface $identity,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private SessionRegistryInterface|null $sessionRegistry = null,
        private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private OidcProviderInterface|null $oidcProvider = null,
        private OAuthClientRegistryInterface|null $clientRegistry = null
    ) {}

    public function execute(BackChannelLogoutData $data) : LogoutResult
    {
        $context = $this->currentAuthentication->read();
        $now = $this->clock->now();
        $claims = $this->oidcProvider?->resolveJwt(jwt: $data->logoutToken);
        $sessionId = is_string($claims['sid'] ?? null) ? trim($claims['sid']) : null;
        $events = $claims['events'] ?? null;
        $backChannelEvent = 'http://schemas.openid.net/event/backchannel-logout';
        $client = $this->resolveClientFromClaims(claims: $claims);

        if (! is_array($events) || ! array_key_exists($backChannelEvent, $events)) {
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
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'aud' => $claims['aud'] ?? null,
                    'client_id' => $client?->clientId,
                    'front_channel_logout_supported' => $client?->frontChannelLogoutSupported,
                    'back_channel_logout_supported' => $client?->backChannelLogoutSupported,
                ]
            ));
        }

        if ($user !== null) {
            $this->identity->clear(context: $context);
        }

        $this->currentAuthentication->clear();

        return new LogoutResult(
            revoked   : $sessionId !== null,
            sessionId : $sessionId
        );
    }

    /**
     * @param array<string, mixed>|null $claims
     */
    private function resolveClientFromClaims(array|null $claims) : \Avax\Auth\System\Capability\OAuth\OAuthClient|null
    {
        if ($claims === null || $this->clientRegistry === null) {
            return null;
        }

        $audience = $claims['aud'] ?? null;
        $clientId = is_string($audience) ? trim($audience) : null;

        if ($clientId === null || $clientId === '') {
            return null;
        }

        return $this->clientRegistry->find(clientId: $clientId);
    }
}
