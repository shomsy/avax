<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\AuthorizeCode;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\IssuedAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthGrantType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\PkceMethod;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthAuthorizationFailed;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ValidateRequestObject\ValidateRequestObject;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ValidateRequestObject\ValidateRequestObjectData;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class AuthorizeCode
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private UserSourceInterface $userSource,
        private OAuthClientRegistryInterface $clientRegistry,
        #[SensitiveParameter]
        private AuthorizationCodeStoreInterface $codeStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private ?OidcProviderInterface $oidcProvider = null,
        private ?ValidateRequestObject $requestObjectValidator = null,
    ) {}

    /**
     * @throws OAuthAuthorizationFailed
     * @throws DateMalformedStringException
     */
    public function execute(AuthorizeCodeData $data): IssuedAuthorizationCode
    {
        $context = $this->currentAuthentication->read();
        $actor = $context->user();
        $now = $this->clock->now();
        if ($data->requestUri !== null && trim(string: $data->requestUri) !== '') {
            if ($this->requestObjectValidator === null) {
                $this->recordFailure(data: $data, reason: 'request_object_not_supported');

                throw OAuthAuthorizationFailed::invalidRequestObject();
            }

            try {
                $requestOverrides = $this->requestObjectValidator->execute(
                    data: new ValidateRequestObjectData(requestUri: $data->requestUri),
                );
            } catch (OAuthAuthorizationFailed) {
                $this->recordFailure(data: $data, reason: 'request_object_invalid');

                throw OAuthAuthorizationFailed::invalidRequestObject();
            }

            $data = new AuthorizeCodeData(
                clientId           : $requestOverrides->clientId,
                redirectUri        : $requestOverrides->redirectUri,
                scopes             : $requestOverrides->scopes,
                state              : $requestOverrides->state,
                nonce              : $requestOverrides->nonce,
                requestUri         : $requestOverrides->requestUri,
                codeChallenge      : $requestOverrides->codeChallenge,
                codeChallengeMethod: $requestOverrides->codeChallengeMethod,
                ipAddress          : $data->ipAddress,
                userAgent          : $data->userAgent,
            );
        }

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

        if (! $client->isActive()) {
            $this->recordFailure(data: $data, reason: 'client_inactive');

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

        if (in_array(needle: 'openid', haystack: $scopes, strict: true)) {
            if ($this->oidcProvider === null) {
                $this->recordFailure(data: $data, reason: 'oidc_provider_not_configured');

                throw OAuthAuthorizationFailed::openIdProviderNotConfigured();
            }

            if ($data->nonce === null || trim(string: $data->nonce) === '') {
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
            phishingResistant  : $context->isPhishingResistant(),
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.authorization_code.issued',
            occurredAt: $now,
            context   : [
                            'client_id' => $client->clientId,
                            'user_id'   => $user->getId()->value,
                            'code_id'   => $issued->codeId,
                            'scope'     => implode(separator: ' ', array: $scopes),
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ],
        ));

        return $issued;
    }

    private function recordFailure(AuthorizeCodeData $data, string $reason): void
    {
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.authorization_code.failed',
            occurredAt: $this->clock->now(),
            context   : [
                            'client_id'  => $data->clientId,
                'redirect_uri' => $data->redirectUri,
                            'reason'     => $reason,
                            'ip_address' => $data->ipAddress,
                            'user_agent' => $data->userAgent,
            ],
        ));
    }

    /**
     * @param list<string> $scopes
     *
     * @return list<string>
     */
    private function normalizeScopes(array $scopes): array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $parts = preg_split(pattern: '/\s+/', subject: trim(string: $scope), flags: PREG_SPLIT_NO_EMPTY);

            if ($parts === false) {
                continue;
            }

            foreach ($parts as $value) {
                if (in_array(needle: $value, haystack: $normalized, strict: true)) {
                    continue;
                }

                $normalized[] = $value;
            }
        }

        sort(array: $normalized);

        return $normalized;
    }
}
