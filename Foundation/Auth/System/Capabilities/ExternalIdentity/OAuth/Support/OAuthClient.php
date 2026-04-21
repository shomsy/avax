<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Registered OAuth client contract.
 */
final readonly class OAuthClient
{
    public string|null                    $requestObjectVerificationKeyPem;
    public string|null                    $secretHash;
    public bool                           $active;
    public string|null                    $approvedBy;
    public DateTimeImmutable|null         $approvedAt;
    public OAuthClientApprovalStatus      $approvalStatus;
    public bool                           $backChannelLogoutSupported;
    public bool                           $frontChannelLogoutSupported;
    public bool                           $requestObjectSignatureRequired;
    public bool                           $phishingResistantRequired;
    public bool                           $workloadIdentity;
    public OAuthSenderConstraintType|null $requiredSenderConstraint;
    public OAuthTokenEndpointAuthMethod   $tokenEndpointAuthMethod;
    /** @var array<string, list<string>> */
    public array                          $audienceScopeBoundaries;
    /** @var list<OAuthGrantType> */
    public array                          $allowedGrantTypes;
    /** @var list<string> */
    public array                          $allowedAudiences;
    public string|null                    $tenantSlug;
    /** @var list<string> */
    public array                          $allowedScopes;
    /** @var list<string> */
    public array                          $redirectUris;
    public OAuthClientType                $type;
    public string                         $name;
    public string                         $clientId;

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
        array                                                   $allowedScopes,
        string|null                                             $tenantSlug = null,
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
        OAuthClientApprovalStatus|null                          $approvalStatus = null,
        DateTimeImmutable|null                                  $approvedAt = null,
        string|null                                             $approvedBy = null,
        bool|null                                               $active = null,
        #[SensitiveParameter] string|null                        $secretHash = null,
        #[SensitiveParameter] string|null                        $requestObjectVerificationKeyPem = null
    )
    {
        $allowedAudiences                      ??= [];
        $allowedGrantTypes                     ??= [];
        $audienceScopeBoundaries               ??= [];
        $tokenEndpointAuthMethod               ??= OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC;
        $workloadIdentity                      ??= false;
        $phishingResistantRequired             ??= false;
        $requestObjectSignatureRequired        ??= false;
        $frontChannelLogoutSupported           ??= false;
        $backChannelLogoutSupported            ??= false;
        $approvalStatus                        ??= OAuthClientApprovalStatus::APPROVED;
        $active                                ??= true;
        $this->clientId                        = $clientId;
        $this->name                            = $name;
        $this->type                            = $type;
        $this->redirectUris                    = $redirectUris;
        $this->allowedScopes                   = $allowedScopes;
        $this->tenantSlug                      = $tenantSlug;
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
        $this->approvalStatus                  = $approvalStatus;
        $this->approvedAt                      = $approvedAt;
        $this->approvedBy                      = $approvedBy;
        $this->active                          = $active;
        $this->secretHash                      = $secretHash;
        $this->requestObjectVerificationKeyPem = $requestObjectVerificationKeyPem;
    }

    public function isPublic() : bool
    {
        return $this->type === OAuthClientType::PUBLIC;
    }

    public function isConfidential() : bool
    {
        return $this->type === OAuthClientType::CONFIDENTIAL;
    }

    public function allowsRedirectUri(string $redirectUri) : bool
    {
        return in_array($redirectUri, $this->redirectUris, true);
    }

    public function allowsGrantType(OAuthGrantType $grantType) : bool
    {
        return in_array($grantType, $this->allowedGrantTypes, true);
    }

    public function requiresAudience() : bool
    {
        return $this->allowedAudiences !== [];
    }

    public function allowsAudience(string|null $audience) : bool
    {
        if ($this->allowedAudiences === []) {
            return $audience === null || trim($audience) === '';
        }

        if ($audience === null || trim($audience) === '') {
            return false;
        }

        return in_array(trim($audience), $this->allowedAudiences, true);
    }

    /**
     * @param list<string> $scopes
     */
    public function allowsAudienceScopes(string|null $audience, array $scopes) : bool
    {
        if (! $this->allowsScopes(scopes: $scopes)) {
            return false;
        }

        $normalizedAudience = trim((string) $audience);

        if ($normalizedAudience === '' || ! isset($this->audienceScopeBoundaries[$normalizedAudience])) {
            return true;
        }

        foreach ($scopes as $scope) {
            if (! in_array($scope, $this->audienceScopeBoundaries[$normalizedAudience], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<string> $scopes
     */
    public function allowsScopes(array $scopes) : bool
    {
        foreach ($scopes as $scope) {
            if (! in_array($scope, $this->allowedScopes, true)) {
                return false;
            }
        }

        return true;
    }

    public function requiresSenderConstraint() : bool
    {
        return $this->requiredSenderConstraint !== null;
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function isApproved() : bool
    {
        return $this->approvalStatus === OAuthClientApprovalStatus::APPROVED;
    }

    public function isPendingApproval() : bool
    {
        return $this->approvalStatus === OAuthClientApprovalStatus::PENDING_APPROVAL;
    }
}
