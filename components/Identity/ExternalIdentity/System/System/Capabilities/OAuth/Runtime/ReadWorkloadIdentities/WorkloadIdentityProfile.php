<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Runtime\ReadWorkloadIdentities;

use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;

final readonly class WorkloadIdentityProfile
{
    /**
     * @param list<string>                $allowedScopes
     * @param list<string>                $allowedAudiences
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(public string $clientId, public string $name, public array $allowedScopes, public array $allowedAudiences, public array $audienceScopeBoundaries, public ?OAuthSenderConstraintType $requiredSenderConstraint, public bool $phishingResistantRequired) {}
}
