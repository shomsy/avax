<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * In-memory OAuth client registry for tests and demos.
 */
final class InMemoryOAuthClientRegistry implements OAuthClientRegistryInterface
{
    /** @var array<string, OAuthClient> */
    private array $clients = [];

    public function __construct(
        #[SensitiveParameter] private readonly PasswordHasher $passwordHasher
    ) {}

    public function register(
        string $name,
        OAuthClientType $type,
        array $redirectUris,
        array $allowedScopes = []
    ) : RegisteredOAuthClient
    {
        $normalizedRedirectUris = $this->normalizeRedirectUris($redirectUris);
        $normalizedScopes       = $this->normalizeScopes($allowedScopes);

        if ($normalizedRedirectUris === []) {
            throw new InvalidArgumentException('OAuth clients require at least one redirect URI.');
        }

        $clientId   = 'oauth_' . bin2hex(random_bytes(12));
        $plainSecret = null;
        $secretHash = null;

        if ($type === OAuthClientType::CONFIDENTIAL) {
            $plainSecret = bin2hex(random_bytes(24));
            $secretHash  = $this->passwordHasher->hash($plainSecret);
        }

        $client = new OAuthClient(
            clientId     : $clientId,
            name         : trim($name),
            type         : $type,
            redirectUris : $normalizedRedirectUris,
            allowedScopes: $normalizedScopes,
            secretHash   : $secretHash
        );

        $this->clients[$clientId] = $client;

        return new RegisteredOAuthClient(
            client         : $client,
            plainTextSecret: $plainSecret
        );
    }

    public function find(string $clientId) : OAuthClient|null
    {
        return $this->clients[$clientId] ?? null;
    }

    public function all() : array
    {
        return array_values($this->clients);
    }

    public function verifySecret(
        string $clientId,
        #[SensitiveParameter] string|null $plainTextSecret
    ) : bool
    {
        $client = $this->find($clientId);

        if ($client === null) {
            return false;
        }

        if ($client->isPublic()) {
            return $plainTextSecret === null || $plainTextSecret === '';
        }

        if ($plainTextSecret === null || $plainTextSecret === '' || $client->secretHash === null) {
            return false;
        }

        return $this->passwordHasher->verify($plainTextSecret, $client->secretHash);
    }

    /**
     * @param list<string> $redirectUris
     * @return list<string>
     */
    private function normalizeRedirectUris(array $redirectUris) : array
    {
        $normalized = [];

        foreach ($redirectUris as $redirectUri) {
            $value = trim($redirectUri);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    /**
     * @param list<string> $allowedScopes
     * @return list<string>
     */
    private function normalizeScopes(array $allowedScopes) : array
    {
        $normalized = [];

        foreach ($allowedScopes as $scope) {
            $value = trim($scope);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort($normalized);

        return $normalized;
    }
}
