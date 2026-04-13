<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use SensitiveParameter;

/**
 * Shared client registry for OAuth flows.
 */
interface OAuthClientRegistryInterface
{
    /**
     * @param list<string> $redirectUris
     * @param list<string> $allowedScopes
     * @param list<string> $allowedAudiences
     * @param list<OAuthGrantType> $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function register(
        string $name,
        OAuthClientType $type,
        array $redirectUris,
        array $allowedScopes = [],
        array $allowedAudiences = [],
        array $allowedGrantTypes = [],
        array $audienceScopeBoundaries = [],
        OAuthSenderConstraintType|null $requiredSenderConstraint = null,
        bool $workloadIdentity = false,
        bool $phishingResistantRequired = false
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
