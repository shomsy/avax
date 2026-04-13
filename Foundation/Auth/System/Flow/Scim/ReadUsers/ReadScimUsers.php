<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ReadUsers;

use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;

final readonly class ReadScimUsers
{
    public function __construct(
        private ScimProvisionedIdentityStoreInterface $identityStore,
        private UserSourceInterface $userSource
    ) {}

    /**
     * @return list<ScimUserProjection>
     */
    public function execute(string $directoryId) : array
    {
        $projections = [];

        foreach ($this->identityStore->allForDirectory($directoryId) as $identity) {
            $user = $this->userSource->findById($identity->userId);

            if ($user === null) {
                continue;
            }

            $projections[] = new ScimUserProjection(
                externalId : $identity->externalId,
                userId     : $user->getId()->value,
                email      : $user->getEmail()->value,
                username   : $user->getUsername(),
                roles      : array_map(static fn ($role) => $role->value, $user->getRoles()),
                groups     : $identity->groups,
                state      : $identity->state
            );
        }

        return $projections;
    }
}
