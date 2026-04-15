<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\SyncGroups;

use Avax\Auth\System\Capability\Scim\ScimDirectoryHealth;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUser;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Flow\Scim\ScimFailed;

final readonly class SyncScimGroups
{
    private ProvisionScimUser                     $provisionScimUser;
    private UserSourceInterface                   $userSource;
    private ScimProvisionedIdentityStoreInterface $identityStore;
    private ScimDirectoryStoreInterface           $directoryStore;

    public function __construct(
        ScimDirectoryStoreInterface           $directoryStore,
        ScimProvisionedIdentityStoreInterface $identityStore,
        UserSourceInterface                   $userSource,
        ProvisionScimUser                     $provisionScimUser
    )
    {
        $this->directoryStore    = $directoryStore;
        $this->identityStore     = $identityStore;
        $this->userSource        = $userSource;
        $this->provisionScimUser = $provisionScimUser;
    }

    /**
     * @throws ScimFailed
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
                                                           state         : $data->state
                                                       ));
    }
}
