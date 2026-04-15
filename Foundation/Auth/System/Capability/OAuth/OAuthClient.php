<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Registered OAuth client contract.
 */
final readonly class OAuthClient
{
    /**
     * @param list<string> $redirectUris
     * @param list<string> $allowedScopes
     * @param list<string> $allowedAudiences
     * @param list<OAuthGrantType> $allowedGrantTypes
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        public string                          $clientId,
        public string                          $name,
        public OAuthClientType                 $type,
        public array                           $redirectUris,
        public array                           $allowedScopes,
        public string|null                     $tenantSlug = null,
        public array                           $allowedAudiences = [],
        public array                           $allowedGrantTypes = [],
        public array                           $audienceScopeBoundaries = [],
        public OAuthTokenEndpointAuthMethod    $tokenEndpointAuthMethod = OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC,
        public OAuthSenderConstraintType|null  $requiredSenderConstraint = null,
        public bool                            $workloadIdentity = false,
        public bool                            $phishingResistantRequired = false,
        public bool                            $requestObjectSignatureRequired = false,
        public bool                            $frontChannelLogoutSupported = false,
        public bool                            $backChannelLogoutSupported = false,
        public OAuthClientApprovalStatus       $approvalStatus = OAuthClientApprovalStatus::APPROVED,
        public DateTimeImmutable|null          $approvedAt = null,
        public string|null                     $approvedBy = null,
        public bool                            $active = true,
        #[SensitiveParameter] public string|null $secretHash = null,
        #[SensitiveParameter] public string|null $requestObjectVerificationKeyPem = null
    ) {}

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
