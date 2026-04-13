<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\AuthorizeCode;

use Avax\Auth\System\Capability\OAuth\AuthorizationCodeStoreInterface;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use Avax\Auth\System\Capability\OAuth\IssuedAuthorizationCode;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\PkceMethod;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthAuthorizationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class AuthorizeCode
{
    public function __construct(
        #[\SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private UserSourceInterface                                    $userSource,
        private OAuthClientRegistryInterface                           $clientRegistry,
        #[\SensitiveParameter] private AuthorizationCodeStoreInterface $codeStore,
        private AuditLogInterface                                      $auditLog,
        private Clock                                                  $clock,
        private OidcProviderInterface|null                             $oidcProvider = null
    ) {}

    /**
     * @throws OAuthAuthorizationFailed
     */
    public function execute(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        $context = $this->currentAuthentication->read();
        $actor   = $context->user();
        $now     = $this->clock->now();

        if ($actor === null) {
            $this->recordFailure(data: $data, reason: 'unauthenticated');
            throw OAuthAuthorizationFailed::unauthenticated();
        }

        $user = $this->userSource->findById(id: new UserId(value: $actor->id));

        if ($user === null || ! $user->isActive()) {
            $this->recordFailure(data: $data, reason: 'user_not_active');
            throw OAuthAuthorizationFailed::unauthenticated();
        }

        $client = $this->clientRegistry->find(clientId: $data->clientId);

        if ($client === null) {
            $this->recordFailure(data: $data, reason: 'client_not_found');
            throw OAuthAuthorizationFailed::invalidClient();
        }

        if (! $client->allowsGrantType(grantType: OAuthGrantType::AUTHORIZATION_CODE)) {
            $this->recordFailure(data: $data, reason: 'grant_type_not_allowed');
            throw OAuthAuthorizationFailed::invalidClient();
        }

        if (! $client->allowsRedirectUri(redirectUri: $data->redirectUri)) {
            $this->recordFailure(data: $data, reason: 'redirect_uri_mismatch');
            throw OAuthAuthorizationFailed::invalidRedirectUri();
        }

        $scopes = $this->normalizeScopes(scopes: $data->scopes);

        if (! $client->allowsScopes(scopes: $scopes)) {
            $this->recordFailure(data: $data, reason: 'scope_mismatch');
            throw OAuthAuthorizationFailed::invalidScopes();
        }

        if (in_array('openid', $scopes, true)) {
            if ($this->oidcProvider === null) {
                $this->recordFailure(data: $data, reason: 'oidc_provider_not_configured');
                throw OAuthAuthorizationFailed::openIdProviderNotConfigured();
            }

            if ($data->nonce === null || trim($data->nonce) === '') {
                $this->recordFailure(data: $data, reason: 'oidc_nonce_required');
                throw OAuthAuthorizationFailed::nonceRequired();
            }
        }

        if ($client->isPublic()) {
            if ($data->codeChallenge === null || $data->codeChallengeMethod !== PkceMethod::S256) {
                $this->recordFailure(data: $data, reason: 'pkce_required');
                throw OAuthAuthorizationFailed::invalidPkce();
            }
        }

        if ($client->phishingResistantRequired && ! $context->isPhishingResistant()) {
            $this->recordFailure(data: $data, reason: 'phishing_resistant_required');
            throw OAuthAuthorizationFailed::phishingResistantRequired();
        }

        $issued = $this->codeStore->issue(
            userId             : $user->getId(),
            clientId           : $client->clientId,
            redirectUri        : $data->redirectUri,
            scopes             : $scopes,
            expiresAt          : $now->modify(modifier: '+5 minutes'),
            state              : $data->state,
            nonce              : $data->nonce,
            codeChallenge      : $data->codeChallenge,
            codeChallengeMethod: $data->codeChallengeMethod,
            mfaVerifiedAt      : $context->mfaVerifiedAt(),
            phishingResistant  : $context->isPhishingResistant()
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.authorization_code.issued',
            occurredAt: $now,
            context   : [
                'client_id' => $client->clientId,
                'user_id' => $user->getId()->value,
                'code_id' => $issued->codeId,
                'scope' => implode(' ', $scopes),
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));

        return $issued;
    }

    /**
     * @param list<string> $scopes
     * @return list<string>
     */
    private function normalizeScopes(array $scopes) : array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $value = trim($scope);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort($normalized);

        return $normalized;
    }

    private function recordFailure(AuthorizeCodeData $data, string $reason) : void
    {
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.authorization_code.failed',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $data->clientId,
                'redirect_uri' => $data->redirectUri,
                'reason' => $reason,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));
    }
}
