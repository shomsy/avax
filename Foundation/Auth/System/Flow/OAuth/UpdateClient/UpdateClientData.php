<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\UpdateClient;

use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\OAuthTokenEndpointAuthMethod;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use SensitiveParameter;

final readonly class UpdateClientData
{
    public string|null                       $requestObjectVerificationKeyPem;
    public bool                              $approvalRequired;
    public bool                              $backChannelLogoutSupported;
    public bool                              $frontChannelLogoutSupported;
    public bool                              $requestObjectSignatureRequired;
    public bool                              $phishingResistantRequired;
    public bool                              $workloadIdentity;
    public OAuthSenderConstraintType|null    $requiredSenderConstraint;
    public OAuthTokenEndpointAuthMethod|null $tokenEndpointAuthMethod;
    public array                             $audienceScopeBoundaries;
    public array                             $allowedGrantTypes;
    public array                             $allowedAudiences;
    public array                             $allowedScopes;
    public string|null                       $tenantSlug;
    public array                             $redirectUris;
    public OAuthClientType                   $type;
    public string                            $name;
    public string                            $clientId;

    /**
     * @param list<string>                $redirectUris
     * @param list<string>                $allowedScopes
     * @param list<string>                $allowedAudiences
     * @param list<OAuthGrantType>        $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        string                                                  $clientId,
        string                                                  $name,
        OAuthClientType                                         $type,
        array                                                   $redirectUris,
        string|null                                             $tenantSlug = null,
        array|null                                              $allowedScopes = null,
        array|null                                              $allowedAudiences = null,
        array|null                                              $allowedGrantTypes = null,
        array|null                                              $audienceScopeBoundaries = null,
        #[SensitiveParameter] OAuthTokenEndpointAuthMethod|null $tokenEndpointAuthMethod = null,
        OAuthSenderConstraintType|null                          $requiredSenderConstraint = null,
        bool|null                                               $workloadIdentity = null,
        bool|null                                               $phishingResistantRequired = null,
        bool|null                                               $requestObjectSignatureRequired = null,
        bool|null                                               $frontChannelLogoutSupported = null,
        bool|null                                               $backChannelLogoutSupported = null,
        bool|null                                               $approvalRequired = null,
        #[SensitiveParameter] string|null                       $requestObjectVerificationKeyPem = null
    )
    {
        $allowedScopes                         ??= [];
        $allowedAudiences                      ??= [];
        $allowedGrantTypes                     ??= [];
        $audienceScopeBoundaries               ??= [];
        $workloadIdentity                      ??= false;
        $phishingResistantRequired             ??= false;
        $requestObjectSignatureRequired        ??= false;
        $frontChannelLogoutSupported           ??= false;
        $backChannelLogoutSupported            ??= false;
        $approvalRequired                      ??= false;
        $this->clientId                        = $clientId;
        $this->name                            = $name;
        $this->type                            = $type;
        $this->redirectUris                    = $redirectUris;
        $this->tenantSlug                      = $tenantSlug;
        $this->allowedScopes                   = $allowedScopes;
        $this->allowedAudiences                = $allowedAudiences;
        $this->allowedGrantTypes               = $allowedGrantTypes;
        $this->audienceScopeBoundaries         = $audienceScopeBoundaries;
        $this->tokenEndpointAuthMethod         = $tokenEndpointAuthMethod;
        $this->requiredSenderConstraint        = $requiredSenderConstraint;
        $this->workloadIdentity                = $workloadIdentity;
        $this->phishingResistantRequired       = $phishingResistantRequired;
        $this->requestObjectSignatureRequired  = $requestObjectSignatureRequired;
        $this->frontChannelLogoutSupported     = $frontChannelLogoutSupported;
        $this->backChannelLogoutSupported      = $backChannelLogoutSupported;
        $this->approvalRequired                = $approvalRequired;
        $this->requestObjectVerificationKeyPem = $requestObjectVerificationKeyPem;
    }
}
