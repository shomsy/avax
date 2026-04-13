<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\JarValidation;

/**
 * Represents a validated signed authorization request.
 */
final readonly class SignedRequest
{
    public function __construct(
        public string   $responseType,
        public string   $clientId,
        public string|null $redirectUri,
        public string   $scope,
        public string|null $state,
        public string|null $nonce,
        public string|null $prompt,
        public int|null $maxAge,
        public array|null $claims
    ) {}

    public function hasRedirectUri() : bool
    {
        return $this->redirectUri !== null;
    }

    public function hasNonce() : bool
    {
        return $this->nonce !== null;
    }
}