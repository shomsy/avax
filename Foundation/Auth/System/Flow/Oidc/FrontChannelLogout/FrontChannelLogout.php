<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\FrontChannelLogout;

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

final readonly class FrontChannelLogout
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

    public function execute(FrontChannelLogoutData $data) : LogoutResult
    {
        $context = $this->currentAuthentication->read();
        $now = $this->clock->now();
        $sessionId = $data->sessionId ?? $context->sessionId();

        if ($sessionId === null && $data->idTokenHint !== null && $this->oidcProvider !== null) {
            $claims = $this->oidcProvider->resolveIdToken(idToken: $data->idTokenHint);
            $sessionId = is_string($claims['sid'] ?? null) ? trim($claims['sid']) : null;
        }

        if ($sessionId !== null && $sessionId !== '') {
            $this->sessionRegistry?->revoke(sessionId: $sessionId, revokedAt: $now, reason: 'oidc_front_channel_logout');
        }

        $client = $this->resolveClientFromIdTokenHint(idTokenHint: $data->idTokenHint);

        if ($context->refreshTokenFamilyId() !== null) {
            $this->refreshTokenStore?->revokeFamily(familyId: $context->refreshTokenFamilyId());
        }

        $user = $context->user();

        if ($user !== null) {
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.oidc.front_channel_logout.succeeded',
                occurredAt: $now,
                context   : [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'state' => $data->state,
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
            revoked                : $sessionId !== null,
            sessionId              : $sessionId,
            postLogoutRedirectUri  : $data->postLogoutRedirectUri,
            state                  : $data->state
        );
    }

    private function resolveClientFromIdTokenHint(string|null $idTokenHint) : \Avax\Auth\System\Capability\OAuth\OAuthClient|null
    {
        if ($idTokenHint === null || trim($idTokenHint) === '' || $this->oidcProvider === null || $this->clientRegistry === null) {
            return null;
        }

        $claims = $this->oidcProvider->resolveIdToken(idToken: $idTokenHint);

        if (! is_string($claims['aud'] ?? null) || trim($claims['aud']) === '') {
            return null;
        }

        return $this->clientRegistry->find(clientId: trim($claims['aud']));
    }
}
