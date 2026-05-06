<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadWorkloadIdentities;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;

final readonly class ReadWorkloadIdentities
{
    public function __construct(private OAuthClientRegistryInterface $oAuthClientRegistry)
    {
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function execute(): array
    {
        $profiles = [];

        foreach ($this->oAuthClientRegistry->all() as $oAuthClient) {
            if (! $oAuthClient->workloadIdentity) {
                continue;
            }

            $profiles[] = new WorkloadIdentityProfile(
                clientId                 : $oAuthClient->clientId,
                name                     : $oAuthClient->name,
                allowedScopes            : $oAuthClient->allowedScopes,
                allowedAudiences         : $oAuthClient->allowedAudiences,
                audienceScopeBoundaries  : $oAuthClient->audienceScopeBoundaries,
                requiredSenderConstraint : $oAuthClient->requiredSenderConstraint,
                phishingResistantRequired: $oAuthClient->phishingResistantRequired,
            );
        }

        usort(
            array   : $profiles,
            callback: static fn (WorkloadIdentityProfile $left, WorkloadIdentityProfile $right): int => strcmp(
                string1: $left->clientId,
                string2: $right->clientId,
            ),
        );

        return $profiles;
    }
}
