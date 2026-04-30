<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientType;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthGrantType;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthTokenEndpointAuthMethod;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use SensitiveParameter;

final readonly class UpdateClientData
{
    public bool $approvalRequired;
    public bool $backChannelLogoutSupported;
    public bool $frontChannelLogoutSupported;
    public bool $requestObjectSignatureRequired;
    public bool $phishingResistantRequired;
    public bool $workloadIdentity;
    /** @var array<string, list<string>> */
    public array $audienceScopeBoundaries;
    /** @var list<OAuthGrantType> */
    public array $allowedGrantTypes;
    /** @var list<string> */
    public array $allowedAudiences;
    /** @var list<string> */
    public array $allowedScopes;

    /**
     * @param list<string>         $redirectUris
     * @param list<string>         $allowedScopes
     * @param list<string>         $allowedAudiences
     * @param list<OAuthGrantType> $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        public string                            $clientId,
        public string                            $name,
        public OAuthClientType                   $type,
        public array                             $redirectUris,
        public string|null                       $tenantSlug = null,
        array                                    $allowedScopes = null,
        array                                    $allowedAudiences = null,
        array                                    $allowedGrantTypes = null,
        array                                    $audienceScopeBoundaries = null,
        #[SensitiveParameter]
        public OAuthTokenEndpointAuthMethod|null $tokenEndpointAuthMethod = null,
        public OAuthSenderConstraintType|null    $requiredSenderConstraint = null,
        bool                                     $workloadIdentity = null,
        bool                                     $phishingResistantRequired = null,
        bool                                     $requestObjectSignatureRequired = null,
        bool                                     $frontChannelLogoutSupported = null,
        bool                                     $backChannelLogoutSupported = null,
        bool                                     $approvalRequired = null,
        #[SensitiveParameter]
        public string|null                       $requestObjectVerificationKeyPem = null,
    )
    {
        $allowedScopes                  ??= [];
        $allowedAudiences               ??= [];
        $allowedGrantTypes              ??= [];
        $audienceScopeBoundaries        ??= [];
        $workloadIdentity               ??= false;
        $phishingResistantRequired      ??= false;
        $requestObjectSignatureRequired ??= false;
        $frontChannelLogoutSupported    ??= false;
        $backChannelLogoutSupported     ??= false;
        $approvalRequired               ??= false;
        $this->allowedScopes                  = $allowedScopes;
        $this->allowedAudiences               = $allowedAudiences;
        $this->allowedGrantTypes              = $allowedGrantTypes;
        $this->audienceScopeBoundaries        = $audienceScopeBoundaries;
        $this->workloadIdentity               = $workloadIdentity;
        $this->phishingResistantRequired      = $phishingResistantRequired;
        $this->requestObjectSignatureRequired = $requestObjectSignatureRequired;
        $this->frontChannelLogoutSupported    = $frontChannelLogoutSupported;
        $this->backChannelLogoutSupported     = $backChannelLogoutSupported;
        $this->approvalRequired               = $approvalRequired;
    }
}
