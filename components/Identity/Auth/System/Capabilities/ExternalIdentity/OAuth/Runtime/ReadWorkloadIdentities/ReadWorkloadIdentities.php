<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientRegistryInterface;

final readonly class ReadWorkloadIdentities
{
    public function __construct(private OAuthClientRegistryInterface $clientRegistry) {}

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
                phishingResistantRequired: $client->phishingResistantRequired,
            );
        }

        usort(
            array   : $profiles,
            callback: static fn (WorkloadIdentityProfile $left, WorkloadIdentityProfile $right) : int => strcmp(
                string1: $left->clientId,
                string2: $right->clientId,
            ),
        );

        return $profiles;
    }
}
