<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\SyncGroups;

use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUser;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Flow\Scim\ScimFailed;

final readonly class SyncScimGroups
{
    public function __construct(
        private ScimDirectoryStoreInterface $directoryStore,
        private ScimProvisionedIdentityStoreInterface $identityStore,
        private UserSourceInterface $userSource,
        private ProvisionScimUser $provisionScimUser
    ) {}

    /**
     * @throws ScimFailed
     */
    public function execute(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        $directory = $this->directoryStore->find($data->directoryId);

        if ($directory === null) {
            throw ScimFailed::unknownDirectory();
        }

        $identity = $this->identityStore->find($directory->directoryId, $data->externalId);

        if ($identity === null) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        $user = $this->userSource->findById($identity->userId);

        if ($user === null) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        return $this->provisionScimUser->execute(new ProvisionScimUserData(
            directoryId    : $data->directoryId,
            directoryToken : $data->directoryToken,
            externalId     : $data->externalId,
            email          : $user->getEmail()->value,
            username       : $user->getUsername(),
            groups         : $data->groups,
            state          : $data->state
        ));
    }
}
