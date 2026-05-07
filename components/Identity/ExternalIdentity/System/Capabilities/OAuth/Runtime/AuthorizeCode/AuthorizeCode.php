<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\AuthorizeCode;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\IssuedAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
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
        private CurrentAuthentication           $currentAuthentication,
        private UserSourceInterface             $userSource,
        private OAuthClientRegistryInterface    $oAuthClientRegistry,
        #[SensitiveParameter]
        private AuthorizationCodeStoreInterface $authorizationCodeStore,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private ?OidcProviderInterface          $oidcProvider = null,
        private ?ValidateRequestObject          $validateRequestObject = null,
    ) {}

    /**
     * @throws OAuthAuthorizationFailed
     * @throws DateMalformedStringException
     */
    public function execute(AuthorizeCodeData $authorizeCodeData) : IssuedAuthorizationCode
    {
        $authenticationContext = $this->currentAuthentication->read();
        $actor                 = $authenticationContext->user();
        $now                   = $this->clock->now();
        if ($authorizeCodeData->requestUri !== null && trim(string: $authorizeCodeData->requestUri) !== '') {
            if (! $this->validateRequestObject instanceof ValidateRequestObject) {
                $this->recordFailure(reason: 'request_object_not_supported', data: $authorizeCodeData);

                throw OAuthAuthorizationFailed::invalidRequestObject();
            }

            try {
                $requestOverrides = $this->validateRequestObject->execute(
                    data: new ValidateRequestObjectData(requestUri: $authorizeCodeData->requestUri),
                );
            } catch (OAuthAuthorizationFailed) {
                $this->recordFailure(reason: 'request_object_invalid', data: $authorizeCodeData);

                throw OAuthAuthorizationFailed::invalidRequestObject();
            }

            $authorizeCodeData = new AuthorizeCodeData(
                clientId           : $requestOverrides->clientId,
                redirectUri        : $requestOverrides->redirectUri,
                scopes             : $requestOverrides->scopes,
                state              : $requestOverrides->state,
                nonce              : $requestOverrides->nonce,
                requestUri         : $requestOverrides->requestUri,
                codeChallenge      : $requestOverrides->codeChallenge,
                codeChallengeMethod: $requestOverrides->codeChallengeMethod,
                ipAddress          : $authorizeCodeData->ipAddress,
                userAgent          : $authorizeCodeData->userAgent,
            );
        }

        if (! $actor instanceof AuthenticatedUser) {
            $this->recordFailure(reason: 'unauthenticated', data: $authorizeCodeData);

            throw OAuthAuthorizationFailed::unauthenticated();
        }

        $user = $this->userSource->findById(id: new UserId(value: $actor->id));

        if (! $user instanceof User || ! $user->isActive()) {
            $this->recordFailure(reason: 'user_not_active', data: $authorizeCodeData);

            throw OAuthAuthorizationFailed::unauthenticated();
        }

        $client = $this->oAuthClientRegistry->find(clientId: $authorizeCodeData->clientId);

        if (! $client instanceof OAuthClient) {
            $this->recordFailure(reason: 'client_not_found', data: $authorizeCodeData);

            throw OAuthAuthorizationFailed::invalidClient();
        }

        if (! $client->isActive()) {
            $this->recordFailure(reason: 'client_inactive', data: $authorizeCodeData);

            throw OAuthAuthorizationFailed::invalidClient();
        }

        if (! $client->allowsGrantType(grantType: OAuthGrantType::AUTHORIZATION_CODE)) {
            $this->recordFailure(reason: 'grant_type_not_allowed', data: $authorizeCodeData);

            throw OAuthAuthorizationFailed::invalidClient();
        }

        if (! $client->allowsRedirectUri(redirectUri: $authorizeCodeData->redirectUri)) {
            $this->recordFailure(reason: 'redirect_uri_mismatch', data: $authorizeCodeData);

            throw OAuthAuthorizationFailed::invalidRedirectUri();
        }

        $scopes = $this->normalizeScopes(scopes: $authorizeCodeData->scopes);

        if (! $client->allowsScopes(scopes: $scopes)) {
            $this->recordFailure(reason: 'scope_mismatch', data: $authorizeCodeData);

            throw OAuthAuthorizationFailed::invalidScopes();
        }

        if (in_array(needle: 'openid', haystack: $scopes, strict: true)) {
            if (! $this->oidcProvider instanceof OidcProviderInterface) {
                $this->recordFailure(reason: 'oidc_provider_not_configured', data: $authorizeCodeData);

                throw OAuthAuthorizationFailed::openIdProviderNotConfigured();
            }

            if ($authorizeCodeData->nonce === null || trim(string: $authorizeCodeData->nonce) === '') {
                $this->recordFailure(reason: 'oidc_nonce_required', data: $authorizeCodeData);

                throw OAuthAuthorizationFailed::nonceRequired();
            }
        }

        if ($client->isPublic() && ($authorizeCodeData->codeChallenge === null || $authorizeCodeData->codeChallengeMethod !== PkceMethod::S256)) {
            $this->recordFailure(reason: 'pkce_required', data: $authorizeCodeData);
            throw OAuthAuthorizationFailed::invalidPkce();
        }

        if ($client->phishingResistantRequired && ! $authenticationContext->isPhishingResistant()) {
            $this->recordFailure(reason: 'phishing_resistant_required', data: $authorizeCodeData);

            throw OAuthAuthorizationFailed::phishingResistantRequired();
        }

        $issuedAuthorizationCode = $this->authorizationCodeStore->issue(
            userId             : $user->getId(),
            clientId           : $client->clientId,
            redirectUri        : $authorizeCodeData->redirectUri,
            scopes             : $scopes,
            expiresAt          : $now->modify(modifier: '+5 minutes'),
            state              : $authorizeCodeData->state,
            nonce              : $authorizeCodeData->nonce,
            codeChallenge      : $authorizeCodeData->codeChallenge,
            mfaVerifiedAt      : $authenticationContext->mfaVerifiedAt(),
            phishingResistant  : $authenticationContext->isPhishingResistant(),
            codeChallengeMethod: $authorizeCodeData->codeChallengeMethod,
        );

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.authorization_code.issued',
                                           occurredAt: $now,
                                           context   : [
                                                           'client_id'  => $client->clientId,
                                                           'user_id'    => $user->getId()->value,
                                                           'code_id'    => $issuedAuthorizationCode->codeId,
                                                           'scope'      => implode(separator: ' ', array: $scopes),
                                                           'ip_address' => $authorizeCodeData->ipAddress,
                                                           'user_agent' => $authorizeCodeData->userAgent,
                                                       ],
                                       ));

        return $issuedAuthorizationCode;
    }

    private function recordFailure(AuthorizeCodeData $authorizeCodeData, string $reason) : void
    {
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.authorization_code.failed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'    => $authorizeCodeData->clientId,
                                                           'redirect_uri' => $authorizeCodeData->redirectUri,
                                                           'reason'       => $reason,
                                                           'ip_address'   => $authorizeCodeData->ipAddress,
                                                           'user_agent'   => $authorizeCodeData->userAgent,
                                                       ],
                                       ));
    }

    /**
     * @param list<string> $scopes
     *
     * @return list<string>
     */
    private function normalizeScopes(array $scopes) : array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $parts = preg_split(pattern: '/\s+/', subject: trim(string: $scope), flags: PREG_SPLIT_NO_EMPTY);

            if ($parts === false) {
                continue;
            }

            foreach ($parts as $part) {
                if (in_array(needle: $part, haystack: $normalized, strict: true)) {
                    continue;
                }

                $normalized[] = $part;
            }
        }

        sort(array: $normalized);

        return $normalized;
    }
}
