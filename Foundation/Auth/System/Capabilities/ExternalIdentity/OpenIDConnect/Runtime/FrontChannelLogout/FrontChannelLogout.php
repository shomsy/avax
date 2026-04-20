<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\FrontChannelLogout;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderInterface;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshTokenStoreInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class FrontChannelLogout
{
    private OAuthClientRegistryInterface|null $clientRegistry;
    private OidcProviderInterface|null        $oidcProvider;
    private RefreshTokenStoreInterface|null   $refreshTokenStore;
    private SessionRegistryInterface|null     $sessionRegistry;
    private Clock                             $clock;
    private AuditLogInterface                 $auditLog;
    private IdentityInterface                 $identity;
    private CurrentAuthentication             $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        IdentityInterface                                     $identity,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        #[SensitiveParameter] SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter] RefreshTokenStoreInterface|null $refreshTokenStore = null,
        OidcProviderInterface|null                            $oidcProvider = null,
        OAuthClientRegistryInterface|null                     $clientRegistry = null
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->identity              = $identity;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->sessionRegistry       = $sessionRegistry;
        $this->refreshTokenStore     = $refreshTokenStore;
        $this->oidcProvider          = $oidcProvider;
        $this->clientRegistry        = $clientRegistry;
    }

    public function execute(FrontChannelLogoutData $data) : LogoutResult
    {
        $context   = $this->currentAuthentication->read();
        $now       = $this->clock->now();
        $sessionId = $data->sessionId ?? $context->sessionId();

        if ($sessionId === null && $data->idTokenHint !== null && $this->oidcProvider !== null) {
            $claims    = $this->oidcProvider->resolveIdToken(idToken: $data->idTokenHint);
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
                                                               'user_id'                        => $user->id,
                                                               'session_id'                     => $sessionId,
                                                               'state'                          => $data->state,
                                                               'client_id'                      => $client?->clientId,
                                                               'front_channel_logout_supported' => $client?->frontChannelLogoutSupported,
                                                               'back_channel_logout_supported'  => $client?->backChannelLogoutSupported,
                                                           ]
                                           ));
        }

        if ($user !== null) {
            $this->identity->clear(context: $context);
        }

        $this->currentAuthentication->clear();

        return new LogoutResult(
            revoked              : $sessionId !== null,
            sessionId            : $sessionId,
            postLogoutRedirectUri: $data->postLogoutRedirectUri,
            state                : $data->state
        );
    }

    private function resolveClientFromIdTokenHint(#[SensitiveParameter] string|null $idTokenHint) : OAuthClient|null
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
