<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryHealth;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimProvisionedIdentityStoreInterface;
use Random\RandomException;

final readonly class SyncScimGroups
{
    public function __construct(private ScimDirectoryStoreInterface $directoryStore, private ScimProvisionedIdentityStoreInterface $identityStore, private UserSourceInterface $userSource, private ProvisionScimUser $provisionScimUser) {}

    /**
     * @param SyncScimGroupsData $data
     *
     * @return ScimProvisioningResult
     * @throws RandomException
     */
    public function execute(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        $directory = $this->directoryStore->find(directoryId: $data->directoryId);

        if ($directory === null) {
            throw ScimFailed::unknownDirectory();
        }

        if ($directory->health === ScimDirectoryHealth::UNAVAILABLE) {
            throw ScimFailed::serviceUnavailable();
        }

        $identity = $this->identityStore->find(directoryId: $directory->directoryId, externalId: $data->externalId);

        if ($identity === null) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        $user = $this->userSource->findById(id: $identity->userId);

        if ($user === null) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        return $this->provisionScimUser->execute(data: new ProvisionScimUserData(
                                                           directoryId   : $data->directoryId,
                                                           directoryToken: $data->directoryToken,
                                                           externalId    : $data->externalId,
                                                           email         : $user->getEmail()->value,
                                                           username      : $user->getUsername(),
                                                           groups        : $data->groups,
                                                           state         : $data->state,
                                                       ));
    }
}
