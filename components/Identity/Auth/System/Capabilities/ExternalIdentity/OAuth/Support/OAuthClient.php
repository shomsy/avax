<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

use Avax\Components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Registered OAuth client contract.
 */
final readonly class OAuthClient
{
    public bool                         $active;
    public OAuthClientApprovalStatus    $approvalStatus;
    public bool                         $backChannelLogoutSupported;
    public bool                         $frontChannelLogoutSupported;
    public bool                         $requestObjectSignatureRequired;
    public bool                         $phishingResistantRequired;
    public bool                         $workloadIdentity;
    public OAuthTokenEndpointAuthMethod $tokenEndpointAuthMethod;
    /** @var array<string, list<string>> */
    public array $audienceScopeBoundaries;
    /** @var list<OAuthGrantType> */
    public array $allowedGrantTypes;
    /** @var list<string> */
    public array $allowedAudiences;

    /**
     * @param list<string>                $redirectUris
     * @param list<string>                $allowedScopes
     * @param list<string>                $allowedAudiences
     * @param list<OAuthGrantType>        $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        public string                                           $clientId,
        public string                                           $name,
        public OAuthClientType                                  $type,
        public array                                            $redirectUris,
        public array                                            $allowedScopes,
        public string|null                                      $tenantSlug = null,
        array|null                                              $allowedAudiences = null,
        array|null                                              $allowedGrantTypes = null,
        array|null                                              $audienceScopeBoundaries = null,
        #[SensitiveParameter] OAuthTokenEndpointAuthMethod|null $tokenEndpointAuthMethod = null,
        public OAuthSenderConstraintType|null                   $requiredSenderConstraint = null,
        bool|null                                               $workloadIdentity = null,
        bool|null                                               $phishingResistantRequired = null,
        bool|null                                               $requestObjectSignatureRequired = null,
        bool|null                                               $frontChannelLogoutSupported = null,
        bool|null                                               $backChannelLogoutSupported = null,
        OAuthClientApprovalStatus|null                          $approvalStatus = null,
        public DateTimeImmutable|null                           $approvedAt = null,
        public string|null                                      $approvedBy = null,
        bool|null                                               $active = null,
        #[SensitiveParameter] public string|null                $secretHash = null,
        #[SensitiveParameter] public string|null                $requestObjectVerificationKeyPem = null
    )
    {
        $allowedAudiences                     ??= [];
        $allowedGrantTypes                    ??= [];
        $audienceScopeBoundaries              ??= [];
        $tokenEndpointAuthMethod              ??= OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC;
        $workloadIdentity                     ??= false;
        $phishingResistantRequired            ??= false;
        $requestObjectSignatureRequired       ??= false;
        $frontChannelLogoutSupported          ??= false;
        $backChannelLogoutSupported           ??= false;
        $approvalStatus                       ??= OAuthClientApprovalStatus::APPROVED;
        $active                               ??= true;
        $this->allowedAudiences               = $allowedAudiences;
        $this->allowedGrantTypes              = $allowedGrantTypes;
        $this->audienceScopeBoundaries        = $audienceScopeBoundaries;
        $this->tokenEndpointAuthMethod        = $tokenEndpointAuthMethod;
        $this->workloadIdentity               = $workloadIdentity;
        $this->phishingResistantRequired      = $phishingResistantRequired;
        $this->requestObjectSignatureRequired = $requestObjectSignatureRequired;
        $this->frontChannelLogoutSupported    = $frontChannelLogoutSupported;
        $this->backChannelLogoutSupported     = $backChannelLogoutSupported;
        $this->approvalStatus                 = $approvalStatus;
        $this->active                         = $active;
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
        return in_array(needle: $redirectUri, haystack: $this->redirectUris, strict: true);
    }

    public function allowsGrantType(OAuthGrantType $grantType) : bool
    {
        return in_array(needle: $grantType, haystack: $this->allowedGrantTypes, strict: true);
    }

    public function requiresAudience() : bool
    {
        return $this->allowedAudiences !== [];
    }

    public function allowsAudience(string|null $audience) : bool
    {
        if ($this->allowedAudiences === []) {
            return $audience === null || trim(string: $audience) === '';
        }

        if ($audience === null || trim(string: $audience) === '') {
            return false;
        }

        return in_array(needle: trim(string: $audience), haystack: $this->allowedAudiences, strict: true);
    }

    /**
     * @param list<string> $scopes
     */
    public function allowsAudienceScopes(string|null $audience, array $scopes) : bool
    {
        if (! $this->allowsScopes(scopes: $scopes)) {
            return false;
        }

        $normalizedAudience = trim(string: (string) $audience);

        if ($normalizedAudience === '' || ! isset($this->audienceScopeBoundaries[$normalizedAudience])) {
            return true;
        }

        return array_all(array: $scopes, callback: fn ($scope) => in_array(needle: $scope, haystack: $this->audienceScopeBoundaries[$normalizedAudience], strict: true));
    }

    /**
     * @param list<string> $scopes
     */
    public function allowsScopes(array $scopes) : bool
    {
        return array_all(array: $scopes, callback: fn ($scope) => in_array(needle: $scope, haystack: $this->allowedScopes, strict: true));
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
