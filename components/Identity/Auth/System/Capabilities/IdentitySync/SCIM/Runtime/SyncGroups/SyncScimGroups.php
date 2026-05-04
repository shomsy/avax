<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryHealth;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;
use Random\RandomException;

final readonly class SyncScimGroups
{
    public function __construct(private ScimDirectoryStoreInterface $scimDirectoryStore, private ScimProvisionedIdentityStoreInterface $scimProvisionedIdentityStore, private UserSourceInterface $userSource, private ProvisionScimUser $provisionScimUser) {}

    /**
     * @throws RandomException
     */
    public function execute(SyncScimGroupsData $syncScimGroupsData) : ScimProvisioningResult
    {
        $directory = $this->scimDirectoryStore->find(directoryId: $syncScimGroupsData->directoryId);

        if (! $directory instanceof ScimDirectory) {
            throw ScimFailed::unknownDirectory();
        }

        if ($directory->health === ScimDirectoryHealth::UNAVAILABLE) {
            throw ScimFailed::serviceUnavailable();
        }

        $identity = $this->scimProvisionedIdentityStore->find(directoryId: $directory->directoryId, externalId: $syncScimGroupsData->externalId);

        if (! $identity instanceof ScimProvisionedIdentity) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        $user = $this->userSource->findById(id: $identity->userId);

        if (! $user instanceof User) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        return $this->provisionScimUser->execute(data: new ProvisionScimUserData(
                                                           directoryId   : $syncScimGroupsData->directoryId,
                                                           directoryToken: $syncScimGroupsData->directoryToken,
                                                           externalId    : $syncScimGroupsData->externalId,
            email         : $user->getEmail()->value,
            username      : $user->getUsername(),
                                                           groups        : $syncScimGroupsData->groups,
                                                           state         : $syncScimGroupsData->state,
        ));
    }
}
