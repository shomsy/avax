<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth\ReadWorkloadIdentities;

use Avax\Auth\System\Capabilities\OAuth\OAuthClientRegistryInterface;

final readonly class ReadWorkloadIdentities
{
    private OAuthClientRegistryInterface $clientRegistry;

    public function __construct(
        OAuthClientRegistryInterface $clientRegistry
    )
    {
        $this->clientRegistry = $clientRegistry;
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function execute() : array
    {
        $profiles = [];

        foreach ($this->clientRegistry->all() as $client) {
            if (! $client->workloadIdentity) {
                continue;
            }

            $profiles[] = new WorkloadIdentityProfile(
                clientId                 : $client->clientId,
                name                     : $client->name,
                allowedScopes            : $client->allowedScopes,
                allowedAudiences         : $client->allowedAudiences,
                audienceScopeBoundaries  : $client->audienceScopeBoundaries,
                requiredSenderConstraint : $client->requiredSenderConstraint,
                phishingResistantRequired: $client->phishingResistantRequired
            );
        }

        usort(
            $profiles,
            static fn (WorkloadIdentityProfile $left, WorkloadIdentityProfile $right) : int => strcmp(
                $left->clientId,
                $right->clientId
            )
        );

        return $profiles;
    }
}
