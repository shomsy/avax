<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\UpdateClient;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthGrantType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthTokenEndpointAuthMethod;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
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
     * @param list<string>                $redirectUris
     * @param list<string>                $allowedScopes
     * @param list<string>                $allowedAudiences
     * @param list<OAuthGrantType>        $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        public string                        $clientId,
        public string                        $name,
        public OAuthClientType               $type,
        public array                         $redirectUris,
        public string|null                       $tenantSlug = null, array|null $allowedScopes = null, array|null $allowedAudiences = null, array|null $allowedGrantTypes = null, array|null $audienceScopeBoundaries = null,
        #[SensitiveParameter]
        public OAuthTokenEndpointAuthMethod|null $tokenEndpointAuthMethod = null,
        public OAuthSenderConstraintType|null    $requiredSenderConstraint = null, bool|null $workloadIdentity = null, bool|null $phishingResistantRequired = null, bool|null $requestObjectSignatureRequired = null, bool|null $frontChannelLogoutSupported = null, bool|null $backChannelLogoutSupported = null, bool|null $approvalRequired = null,
        #[SensitiveParameter]
        public string|null                       $requestObjectVerificationKeyPem = null,
    )
    {
        $allowedScopes                        ??= [];
        $allowedAudiences                     ??= [];
        $allowedGrantTypes                    ??= [];
        $audienceScopeBoundaries              ??= [];
        $workloadIdentity                     ??= false;
        $phishingResistantRequired            ??= false;
        $requestObjectSignatureRequired       ??= false;
        $frontChannelLogoutSupported          ??= false;
        $backChannelLogoutSupported           ??= false;
        $approvalRequired                     ??= false;
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
