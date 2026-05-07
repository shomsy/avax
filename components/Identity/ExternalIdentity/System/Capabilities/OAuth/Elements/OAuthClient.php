<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Registered OAuth client contract.
 */
final readonly class OAuthClient
{
    public bool $active;

    public OAuthClientApprovalStatus $approvalStatus;

    public bool $backChannelLogoutSupported;

    public bool $frontChannelLogoutSupported;

    public bool $requestObjectSignatureRequired;

    public bool $phishingResistantRequired;

    public bool $workloadIdentity;

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
        public string                     $clientId,
        public string                     $name,
        public OAuthClientType            $type,
        public array                      $redirectUris,
        public array                      $allowedScopes,
        public ?string                    $tenantSlug = null,
        ?array                            $allowedAudiences = null,
        ?array                            $allowedGrantTypes = null,
        ?array                            $audienceScopeBoundaries = null,
        #[SensitiveParameter]
        ?OAuthTokenEndpointAuthMethod     $oAuthTokenEndpointAuthMethod = null,
        public ?OAuthSenderConstraintType $requiredSenderConstraint = null,
        ?bool                             $workloadIdentity = null,
        ?bool                             $phishingResistantRequired = null,
        ?bool                             $requestObjectSignatureRequired = null,
        ?bool                             $frontChannelLogoutSupported = null,
        ?bool                             $backChannelLogoutSupported = null,
        ?OAuthClientApprovalStatus        $oAuthClientApprovalStatus = null,
        public ?DateTimeImmutable         $approvedAt = null,
        public ?string                    $approvedBy = null,
        ?bool                             $active = null,
        #[SensitiveParameter]
        public ?string                    $secretHash = null,
        #[SensitiveParameter]
        public ?string                    $requestObjectVerificationKeyPem = null,
    )
    {
        $allowedAudiences                     ??= [];
        $allowedGrantTypes                    ??= [];
        $audienceScopeBoundaries              ??= [];
        $oAuthTokenEndpointAuthMethod         ??= OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC;
        $workloadIdentity                     ??= false;
        $phishingResistantRequired            ??= false;
        $requestObjectSignatureRequired       ??= false;
        $frontChannelLogoutSupported          ??= false;
        $backChannelLogoutSupported           ??= false;
        $oAuthClientApprovalStatus            ??= OAuthClientApprovalStatus::APPROVED;
        $active                               ??= true;
        $this->allowedAudiences               = $allowedAudiences;
        $this->allowedGrantTypes              = $allowedGrantTypes;
        $this->audienceScopeBoundaries        = $audienceScopeBoundaries;
        $this->tokenEndpointAuthMethod        = $oAuthTokenEndpointAuthMethod;
        $this->workloadIdentity               = $workloadIdentity;
        $this->phishingResistantRequired      = $phishingResistantRequired;
        $this->requestObjectSignatureRequired = $requestObjectSignatureRequired;
        $this->frontChannelLogoutSupported    = $frontChannelLogoutSupported;
        $this->backChannelLogoutSupported     = $backChannelLogoutSupported;
        $this->approvalStatus                 = $oAuthClientApprovalStatus;
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

    public function allowsGrantType(OAuthGrantType $oAuthGrantType) : bool
    {
        return in_array(needle: $oAuthGrantType, haystack: $this->allowedGrantTypes, strict: true);
    }

    public function requiresAudience() : bool
    {
        return $this->allowedAudiences !== [];
    }

    public function allowsAudience(?string $audience) : bool
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
    public function allowsAudienceScopes(?string $audience, array $scopes) : bool
    {
        if (! $this->allowsScopes(scopes: $scopes)) {
            return false;
        }

        $normalizedAudience = trim(string: (string) $audience);

        if ($normalizedAudience === '' || ! isset($this->audienceScopeBoundaries[$normalizedAudience])) {
            return true;
        }

        return array_all(array: $scopes, callback: fn ($scope) : bool => in_array(needle: $scope, haystack: $this->audienceScopeBoundaries[$normalizedAudience], strict: true));
    }

    /**
     * @param list<string> $scopes
     */
    public function allowsScopes(array $scopes) : bool
    {
        return array_all(array: $scopes, callback: fn ($scope) : bool => in_array(needle: $scope, haystack: $this->allowedScopes, strict: true));
    }

    public function requiresSenderConstraint() : bool
    {
        return $this->requiredSenderConstraint instanceof OAuthSenderConstraintType;
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
