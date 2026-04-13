<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ReadWorkloadIdentities;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;

final readonly class WorkloadIdentityProfile
{
    /**
     * @param list<string> $allowedScopes
     * @param list<string> $allowedAudiences
     * @param array<string, list<string>> $audienceScopeBoundaries
     */
    public function __construct(
        public string $clientId,
        public string $name,
        public array $allowedScopes,
        public array $allowedAudiences,
        public array $audienceScopeBoundaries,
        public OAuthSenderConstraintType|null $requiredSenderConstraint,
        public bool $phishingResistantRequired
    ) {}
}
