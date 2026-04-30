<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ValidateRequestObject;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthAuthorizationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\PkceMethod;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcRequestObjectStoreInterface;

final readonly class ValidateRequestObject
{
    public function __construct(private OidcRequestObjectStoreInterface|null $requestObjectStore = null, private OAuthClientRegistryInterface|null $clientRegistry = null) {}

    public function execute(ValidateRequestObjectData $data) : ValidatedRequestObject
    {
        $requestUri = trim(string: $data->requestUri);

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
            codeChallengeMethod: $this->normalizeCodeChallengeMethod(value: $claims['code_challenge_method'] ?? null),
        );
    }

    private function readStringValue(mixed $value) : string|null
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
        if (is_array(value: $value)) {
            return array_values(array: $value);
        }

        if (! is_string(value: $value) || trim(string: $value) === '') {
            return [];
        }

        $parts = preg_split(pattern: '/\s+/', subject: trim(string: $value), flags: PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : $parts;
    }

    private function normalizeCodeChallengeMethod(string|null $value) : PkceMethod|null
    {
        if ($value === null || trim(string: $value) === '') {
            return null;
        }

        return PkceMethod::tryFrom(value: trim(string: $value));
    }
}
