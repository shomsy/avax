<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
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
        string                        $name,
        OAuthClientType               $oAuthClientType,
        array $redirectUris, string|null $tenantSlug = null,
        array                         $allowedScopes = [],
        array                         $allowedAudiences = [],
        array                         $allowedGrantTypes = [],
        array $audienceScopeBoundaries = [], OAuthTokenEndpointAuthMethod|null $oAuthTokenEndpointAuthMethod = null, OAuthSenderConstraintType|null $oAuthSenderConstraintType = null,
        bool                          $workloadIdentity = false,
        bool                          $phishingResistantRequired = false,
        bool                          $requestObjectSignatureRequired = false,
        bool                          $frontChannelLogoutSupported = false,
        bool  $backChannelLogoutSupported = false, bool|null $approvalRequired = null,
        #[SensitiveParameter]
        ?string                       $requestObjectVerificationKeyPem = null,
    ) : RegisteredOAuthClient;

    public function replace(OAuthClient $oAuthClient) : void;

    public function deactivate(string $clientId) : ?OAuthClient;

    public function approve(string $clientId, string $approvedBy) : ?OAuthClient;

    public function rotateSecret(string $clientId) : ?RegisteredOAuthClient;

    public function find(string $clientId) : ?OAuthClient;

    /**
     * @return list<OAuthClient>
     */
    public function all() : array;

    public function verifySecret(
        string  $clientId,
        #[SensitiveParameter]
        ?string $plainTextSecret,
    ) : bool;
}
