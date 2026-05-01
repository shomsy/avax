<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RegisterClient;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthGrantType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthTokenEndpointAuthMethod;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
use SensitiveParameter;

final readonly class RegisterClientData
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
     * @param list<string>                $redirectUris
     * @param list<string>                $allowedScopes
     * @param list<string>                $allowedAudiences
     * @param list<OAuthGrantType>        $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        public string $name,
        public OAuthClientType $type,
        public array $redirectUris,
        public ?string $tenantSlug = null,
        ?array $allowedScopes = null,
        ?array $allowedAudiences = null,
        ?array $allowedGrantTypes = null,
        ?array $audienceScopeBoundaries = null,
        #[SensitiveParameter]
        public ?OAuthTokenEndpointAuthMethod $tokenEndpointAuthMethod = null,
        public ?OAuthSenderConstraintType $requiredSenderConstraint = null,
        ?bool  $workloadIdentity = null,
        ?bool  $phishingResistantRequired = null,
        ?bool  $requestObjectSignatureRequired = null,
        ?bool  $frontChannelLogoutSupported = null,
        ?bool  $backChannelLogoutSupported = null,
        ?bool  $approvalRequired = null,
        #[SensitiveParameter]
        public ?string $requestObjectVerificationKeyPem = null,
    ) {
        $allowedScopes                     ??= [];
        $allowedAudiences                  ??= [];
        $allowedGrantTypes                 ??= [];
        $audienceScopeBoundaries           ??= [];
        $workloadIdentity                  ??= false;
        $phishingResistantRequired         ??= false;
        $requestObjectSignatureRequired ??= false;
        $frontChannelLogoutSupported       ??= false;
        $backChannelLogoutSupported        ??= false;
        $approvalRequired                  ??= false;
        $this->allowedScopes               = $allowedScopes;
        $this->allowedAudiences            = $allowedAudiences;
        $this->allowedGrantTypes           = $allowedGrantTypes;
        $this->audienceScopeBoundaries     = $audienceScopeBoundaries;
        $this->workloadIdentity            = $workloadIdentity;
        $this->phishingResistantRequired   = $phishingResistantRequired;
        $this->requestObjectSignatureRequired = $requestObjectSignatureRequired;
        $this->frontChannelLogoutSupported = $frontChannelLogoutSupported;
        $this->backChannelLogoutSupported  = $backChannelLogoutSupported;
        $this->approvalRequired            = $approvalRequired;
    }
}
