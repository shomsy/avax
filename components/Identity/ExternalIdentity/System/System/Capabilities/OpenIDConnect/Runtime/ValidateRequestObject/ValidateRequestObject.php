<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Runtime\ValidateRequestObject;

use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\PkceMethod;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Runtime\OAuthAuthorizationFailed;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Protocol\OidcRequestObject;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Protocol\OidcRequestObjectStoreInterface;

final readonly class ValidateRequestObject
{
    public function __construct(private ?OidcRequestObjectStoreInterface $oidcRequestObjectStore = null, private ?OAuthClientRegistryInterface $oAuthClientRegistry = null) {}

    public function execute(ValidateRequestObjectData $validateRequestObjectData) : ValidatedRequestObject
    {
        $requestUri = trim(string: $validateRequestObjectData->requestUri);

        if ($requestUri === '' || ! $this->oidcRequestObjectStore instanceof OidcRequestObjectStoreInterface) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $requestObject = $this->oidcRequestObjectStore->consume(requestUri: $requestUri);

        if (! $requestObject instanceof OidcRequestObject) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $claims      = $requestObject->claims;
        $clientId    = $this->readStringValue(value: $claims['client_id'] ?? null);
        $redirectUri = $this->readStringValue(value: $claims['redirect_uri'] ?? null);

        if ($clientId === null || $redirectUri === null) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $client = $this->oAuthClientRegistry?->find(clientId: $clientId);

        if ($client?->requestObjectSignatureRequired === true && ! $requestObject->signatureVerified) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        if ($requestObject->signatureVerified && $requestObject->signingClientId !== null && $requestObject->signingClientId !== $clientId) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        return new ValidatedRequestObject(
            requestUri         : $requestUri,
            clientId           : $clientId,
            redirectUri        : $redirectUri,
            scopes             : $this->normalizeScopes(scopes: $this->normalizeScopeValue(value: $claims['scope'] ?? null)),
            state              : $this->readStringValue(value: $claims['state'] ?? null),
            nonce              : $this->readStringValue(value: $claims['nonce'] ?? null),
            codeChallenge      : $this->readStringValue(value: $claims['code_challenge'] ?? null),
            codeChallengeMethod: $this->normalizeCodeChallengeMethod(value: $claims['code_challenge_method'] ?? null),
        );
    }

    private function readStringValue(mixed $value) : ?string
    {
        if (! is_string(value: $value)) {
            return null;
        }

        $normalized = trim(string: $value);

        return $normalized !== '' ? $normalized : null;
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

    /**
     * @param array<int, string>|string|null $value
     *
     * @return list<string>
     */
    private function normalizeScopeValue(string|array|null $value) : array
    {
        if (is_array(value: $value)) {
            return array_values(array: $value);
        }

        if (! is_string(value: $value) || trim(string: $value) === '') {
            return [];
        }

        $parts = preg_split(pattern: '/\s+/', subject: trim(string: $value), flags: PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : $parts;
    }

    private function normalizeCodeChallengeMethod(?string $value) : ?PkceMethod
    {
        if ($value === null || trim(string: $value) === '') {
            return null;
        }

        return PkceMethod::tryFrom(value: trim(string: $value));
    }
}
