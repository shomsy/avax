<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;

/**
 * Registered OAuth client contract.
 */
final readonly class OAuthClient
{
    /**
     * @param list<string> $redirectUris
     * @param list<string> $allowedScopes
     * @param list<OAuthGrantType> $allowedGrantTypes
     */
    public function __construct(
        public string                          $clientId,
        public string                          $name,
        public OAuthClientType                 $type,
        public array                           $redirectUris,
        public array                           $allowedScopes,
        public array                           $allowedGrantTypes = [],
        public OAuthSenderConstraintType|null  $requiredSenderConstraint = null,
        public bool                            $workloadIdentity = false,
        public bool                            $phishingResistantRequired = false,
        public string|null                     $secretHash = null
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

    public function requiresSenderConstraint() : bool
    {
        return $this->requiredSenderConstraint !== null;
    }
}
