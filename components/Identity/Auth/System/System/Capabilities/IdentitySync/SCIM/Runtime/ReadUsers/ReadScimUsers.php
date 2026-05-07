<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers;

use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;

final readonly class ReadScimUsers
{
    public function __construct(private ScimProvisionedIdentityStoreInterface $scimProvisionedIdentityStore, private UserSourceInterface $userSource) {}

    /**
     * @return list<ScimUserProjection>
     */
    public function execute(string $directoryId) : array
    {
        $projections = [];

        foreach ($this->scimProvisionedIdentityStore->allForDirectory(directoryId: $directoryId) as $scimProvisionedIdentity) {
            $user = $this->userSource->findById(id: $scimProvisionedIdentity->userId);

            if (! $user instanceof User) {
                continue;
            }

            $projections[] = new ScimUserProjection(
                externalId: $scimProvisionedIdentity->externalId,
                userId    : $user->getId()->value,
                email     : $user->getEmail()->value,
                username  : $user->getUsername(),
                roles     : array_map(callback: static fn (UserRole $userRole) => $userRole->value, array: $user->getRoles()),
                groups    : $scimProvisionedIdentity->groups,
                state     : $scimProvisionedIdentity->state,
            );
        }

        return $projections;
    }
}
