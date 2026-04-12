<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use SensitiveParameter;

/**
 * Shared client registry for OAuth flows.
 */
interface OAuthClientRegistryInterface
{
    /**
     * @param list<string> $redirectUris
     * @param list<string> $allowedScopes
     */
    public function register(
        string $name,
        OAuthClientType $type,
        array $redirectUris,
        array $allowedScopes = []
    ) : RegisteredOAuthClient;

    public function find(string $clientId) : OAuthClient|null;

    /**
     * @return list<OAuthClient>
     */
    public function all() : array;

    public function verifySecret(
        string $clientId,
        #[SensitiveParameter] string|null $plainTextSecret
    ) : bool;
}
