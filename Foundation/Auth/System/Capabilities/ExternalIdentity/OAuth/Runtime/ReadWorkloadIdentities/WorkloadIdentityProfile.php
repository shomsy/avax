<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;

final readonly class WorkloadIdentityProfile
{
    public bool                           $phishingResistantRequired;
    public OAuthSenderConstraintType|null $requiredSenderConstraint;
    /** @var array<string, list<string>> */
    public array                          $audienceScopeBoundaries;
    /** @var list<string> */
    public array                          $allowedAudiences;
    /** @var list<string> */
    public array                          $allowedScopes;
    public string                         $name;
    public string                         $clientId;

    /**
     * @param list<string>                $allowedScopes
     * @param list<string>                $allowedAudiences
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        string                         $clientId,
        string                         $name,
        array                          $allowedScopes,
        array                          $allowedAudiences,
        array                          $audienceScopeBoundaries,
        OAuthSenderConstraintType|null $requiredSenderConstraint,
        bool                           $phishingResistantRequired
    )
    {
        $this->clientId                  = $clientId;
        $this->name                      = $name;
        $this->allowedScopes             = $allowedScopes;
        $this->allowedAudiences          = $allowedAudiences;
        $this->audienceScopeBoundaries   = $audienceScopeBoundaries;
        $this->requiredSenderConstraint  = $requiredSenderConstraint;
        $this->phishingResistantRequired = $phishingResistantRequired;
    }
}
