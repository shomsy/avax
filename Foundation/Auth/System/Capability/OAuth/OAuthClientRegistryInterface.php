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
     * @param list<string>                $redirectUris
     * @param list<string>                $allowedScopes
     * @param list<string>                $allowedAudiences
     * @param list<OAuthGrantType>        $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function register(
        string                            $name,
        OAuthClientType                   $type,
        array                             $redirectUris,
        string|null                       $tenantSlug = null,
        array                             $allowedScopes = [],
        array                             $allowedAudiences = [],
        array                             $allowedGrantTypes = [],
        array                             $audienceScopeBoundaries = [],
        OAuthTokenEndpointAuthMethod|null $tokenEndpointAuthMethod = null,
        OAuthSenderConstraintType|null    $requiredSenderConstraint = null,
        bool                              $workloadIdentity = false,
        bool                              $phishingResistantRequired = false,
        bool                              $requestObjectSignatureRequired = false,
        bool                              $frontChannelLogoutSupported = false,
        bool                              $backChannelLogoutSupported = false,
        bool|null                         $approvalRequired = null,
        #[SensitiveParameter] string|null $requestObjectVerificationKeyPem = null
    ) : RegisteredOAuthClient;

    public function replace(OAuthClient $client) : void;

    public function deactivate(string $clientId) : OAuthClient|null;

    public function approve(string $clientId, string $approvedBy) : OAuthClient|null;

    public function rotateSecret(string $clientId) : RegisteredOAuthClient|null;

    public function find(string $clientId) : OAuthClient|null;

    /**
     * @return list<OAuthClient>
     */
    public function all() : array;

    public function verifySecret(
        string                            $clientId,
        #[SensitiveParameter] string|null $plainTextSecret
    ) : bool;
}
