<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers;

use components\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimProvisionedIdentityStoreInterface;

final readonly class ReadScimUsers
{
    public function __construct(private ScimProvisionedIdentityStoreInterface $identityStore, private UserSourceInterface $userSource) {}

    /**
     * @return list<ScimUserProjection>
     */
    public function execute(string $directoryId) : array
    {
        $projections = [];

        foreach ($this->identityStore->allForDirectory(directoryId: $directoryId) as $identity) {
            $user = $this->userSource->findById(id: $identity->userId);

            if ($user === null) {
                continue;
            }

            $projections[] = new ScimUserProjection(
                externalId: $identity->externalId,
                userId    : $user->getId()->value,
                email     : $user->getEmail()->value,
                username  : $user->getUsername(),
                roles     : array_map(callback: static fn ($role) => $role->value, array: $user->getRoles()),
                groups    : $identity->groups,
                state     : $identity->state
            );
        }

        return $projections;
    }
}
