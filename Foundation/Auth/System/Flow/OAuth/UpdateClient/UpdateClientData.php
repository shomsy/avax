<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\UpdateClient;

use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\OAuth\OAuthTokenEndpointAuthMethod;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;

final readonly class UpdateClientData
{
    /**
     * @param list<string> $redirectUris
     * @param list<string> $allowedScopes
     * @param list<string> $allowedAudiences
     * @param list<OAuthGrantType> $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        public string $clientId,
        public string $name,
        public OAuthClientType $type,
        public array $redirectUris,
        public string|null $tenantSlug = null,
        public array $allowedScopes = [],
        public array $allowedAudiences = [],
        public array $allowedGrantTypes = [],
        public array $audienceScopeBoundaries = [],
        public OAuthTokenEndpointAuthMethod|null $tokenEndpointAuthMethod = null,
        public OAuthSenderConstraintType|null $requiredSenderConstraint = null,
        public bool $workloadIdentity = false,
        public bool $phishingResistantRequired = false,
        public bool $frontChannelLogoutSupported = false,
        public bool $backChannelLogoutSupported = false,
        public bool $approvalRequired = false
    ) {}
}
