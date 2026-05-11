<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM;

use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\IdentitySyncCapabilityUnavailable;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\RegisteredScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\RunScimBulk;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutage;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadDirectories\ReadScimDirectories;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ReadScimGroups;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ReadScimUsers;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutage;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotateScimToken;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroups;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData;
use Random\RandomException;
use SensitiveParameter;

final readonly class SCIM
{
    public function __construct(
        private RegisterScimDirectory|null      $registerScimDirectory,
        private ReadScimDirectories|null        $readScimDirectories,
        #[SensitiveParameter]
        private RotateScimToken|null            $rotateScimToken,
        private MarkScimDirectoryOutage|null    $markScimDirectoryOutage,
        private RecoverScimDirectoryOutage|null $recoverScimDirectoryOutage,
        private ProvisionScimUser|null          $provisionScimUser,
        private DeleteScimUser|null             $deleteScimUser,
        private ReadScimUsers|null              $readScimUsers,
        private ReadScimGroups|null             $readScimGroups,
        private SyncScimGroups|null             $syncScimGroups,
        private RunScimBulk|null                $runScimBulk,
    ) {}

    public function isConfigured() : bool
    {
        return $this->registerScimDirectory instanceof RegisterScimDirectory
            && $this->readScimDirectories instanceof ReadScimDirectories
            && $this->rotateScimToken instanceof RotateScimToken
            && $this->markScimDirectoryOutage instanceof MarkScimDirectoryOutage
            && $this->recoverScimDirectoryOutage instanceof RecoverScimDirectoryOutage
            && $this->provisionScimUser instanceof ProvisionScimUser
            && $this->deleteScimUser instanceof DeleteScimUser
            && $this->readScimUsers instanceof ReadScimUsers
            && $this->readScimGroups instanceof ReadScimGroups
            && $this->syncScimGroups instanceof SyncScimGroups
            && $this->runScimBulk instanceof RunScimBulk;
    }

    /**
     * @throws RandomException
     */
    public function registerDirectory(RegisterScimDirectoryData $registerScimDirectoryData) : RegisteredScimDirectory
    {
        return $this->registerScimDirectoryOrFail()->execute(data: $registerScimDirectoryData);
    }

    private function registerScimDirectoryOrFail() : RegisterScimDirectory
    {
        return $this->registerScimDirectory ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'register_directory');
    }

    /**
     * @return list<ScimDirectory>
     */
    public function readDirectories(string|null $tenantSlug = null) : array
    {
        return $this->readScimDirectoriesOrFail()->execute(tenantSlug: $tenantSlug);
    }

    private function readScimDirectoriesOrFail() : ReadScimDirectories
    {
        return $this->readScimDirectories ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'read_directories');
    }

    /**
     * @throws RandomException
     */
    public function rotateToken(string $directoryId) : RotatedScimToken
    {
        return $this->rotateScimTokenOrFail()->execute(directoryId: $directoryId);
    }

    private function rotateScimTokenOrFail() : RotateScimToken
    {
        return $this->rotateScimToken ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'rotate_token');
    }

    public function markDirectoryOutage(MarkScimDirectoryOutageData $markScimDirectoryOutageData) : ScimDirectory
    {
        return $this->markScimDirectoryOutageOrFail()->execute(data: $markScimDirectoryOutageData);
    }

    private function markScimDirectoryOutageOrFail() : MarkScimDirectoryOutage
    {
        return $this->markScimDirectoryOutage ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'mark_directory_outage');
    }

    public function recoverDirectoryOutage(RecoverScimDirectoryOutageData $recoverScimDirectoryOutageData) : ScimDirectory
    {
        return $this->recoverScimDirectoryOutageOrFail()->execute(data: $recoverScimDirectoryOutageData);
    }

    private function recoverScimDirectoryOutageOrFail() : RecoverScimDirectoryOutage
    {
        return $this->recoverScimDirectoryOutage ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'recover_directory_outage');
    }

    /**
     * @throws RandomException
     */
    public function provisionUser(ProvisionScimUserData $provisionScimUserData) : ScimProvisioningResult
    {
        return $this->provisionScimUserOrFail()->execute(data: $provisionScimUserData);
    }

    private function provisionScimUserOrFail() : ProvisionScimUser
    {
        return $this->provisionScimUser ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'provision_user');
    }

    public function deleteUser(DeleteScimUserData $deleteScimUserData) : void
    {
        $this->deleteScimUserOrFail()->execute(data: $deleteScimUserData);
    }

    private function deleteScimUserOrFail() : DeleteScimUser
    {
        return $this->deleteScimUser ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'delete_user');
    }

    /**
     * @return list<ScimUserProjection>
     */
    public function readUsers(string $directoryId) : array
    {
        return $this->readScimUsersOrFail()->execute(directoryId: $directoryId);
    }

    private function readScimUsersOrFail() : ReadScimUsers
    {
        return $this->readScimUsers ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'read_users');
    }

    /**
     * @return list<ScimGroupProjection>
     */
    public function readGroups(string $directoryId) : array
    {
        return $this->readScimGroupsOrFail()->execute(directoryId: $directoryId);
    }

    private function readScimGroupsOrFail() : ReadScimGroups
    {
        return $this->readScimGroups ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'read_groups');
    }

    /**
     * @throws RandomException
     */
    public function syncGroups(SyncScimGroupsData $syncScimGroupsData) : ScimProvisioningResult
    {
        return $this->syncScimGroupsOrFail()->execute(data: $syncScimGroupsData);
    }

    private function syncScimGroupsOrFail() : SyncScimGroups
    {
        return $this->syncScimGroups ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'sync_groups');
    }

    public function runBulk(ScimBulkRequest $scimBulkRequest) : ScimBulkResponse
    {
        return $this->runScimBulkOrFail()->execute(request: $scimBulkRequest);
    }

    private function runScimBulkOrFail() : RunScimBulk
    {
        return $this->runScimBulk ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'run_bulk');
    }
}
