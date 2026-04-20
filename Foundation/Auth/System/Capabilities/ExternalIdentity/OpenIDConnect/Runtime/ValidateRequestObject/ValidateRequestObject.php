<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Oidc\ValidateRequestObject;

use Avax\Auth\System\Capabilities\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capabilities\OAuth\PkceMethod;
use Avax\Auth\System\Capabilities\Oidc\OidcRequestObjectStoreInterface;
use Avax\Auth\System\Flows\OAuth\OAuthAuthorizationFailed;

final readonly class ValidateRequestObject
{
    private OAuthClientRegistryInterface|null    $clientRegistry;
    private OidcRequestObjectStoreInterface|null $requestObjectStore;

    public function __construct(
        OidcRequestObjectStoreInterface|null $requestObjectStore = null,
        OAuthClientRegistryInterface|null    $clientRegistry = null
    )
    {
        $this->requestObjectStore = $requestObjectStore;
        $this->clientRegistry     = $clientRegistry;
    }

    public function execute(ValidateRequestObjectData $data) : ValidatedRequestObject
    {
        $requestUri = trim($data->requestUri);

        if ($requestUri === '' || $this->requestObjectStore === null) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $requestObject = $this->requestObjectStore->consume(requestUri: $requestUri);

        if ($requestObject === null) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $claims      = $requestObject->claims;
        $clientId    = $this->readStringValue(value: $claims['client_id'] ?? null);
        $redirectUri = $this->readStringValue(value: $claims['redirect_uri'] ?? null);

        if ($clientId === null || $redirectUri === null) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $client = $this->clientRegistry?->find(clientId: $clientId);

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
            codeChallengeMethod: $this->normalizeCodeChallengeMethod(value: $claims['code_challenge_method'] ?? null)
        );
    }

    private function readStringValue(mixed $value) : string|null
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

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
            $parts = preg_split(pattern: '/\s+/', subject: trim($scope), flags: PREG_SPLIT_NO_EMPTY);

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

    /**
     * @param array<int, string>|string|null $value
     *
     * @return list<string>
     */
    private function normalizeScopeValue(string|array|null $value) : array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $parts = preg_split(pattern: '/\s+/', subject: trim($value), flags: PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : $parts;
    }

    private function normalizeCodeChallengeMethod(string|null $value) : PkceMethod|null
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return PkceMethod::tryFrom(value: trim($value));
    }
}
