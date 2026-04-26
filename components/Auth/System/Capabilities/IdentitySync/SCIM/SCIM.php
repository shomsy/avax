<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM;

use Avax\Auth\System\Capabilities\IdentitySync\IdentitySyncCapabilityUnavailable;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\RunScimBulk;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUser;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutage;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUser;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadDirectories\ReadScimDirectories;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ReadScimGroups;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ReadScimUsers;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutage;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotateScimToken;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroups;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Random\RandomException;
use SensitiveParameter;

final readonly class SCIM
{
    public function __construct(
        private RegisterScimDirectory|null                 $registerScimDirectory,
        private ReadScimDirectories|null                   $readScimDirectories,
        #[SensitiveParameter] private RotateScimToken|null $rotateScimToken,
        private MarkScimDirectoryOutage|null               $markScimDirectoryOutage,
        private RecoverScimDirectoryOutage|null            $recoverScimDirectoryOutage,
        private ProvisionScimUser|null                     $provisionScimUser,
        private DeleteScimUser|null                        $deleteScimUser,
        private ReadScimUsers|null                         $readScimUsers,
        private ReadScimGroups|null                        $readScimGroups,
        private SyncScimGroups|null                        $syncScimGroups,
        private RunScimBulk|null                           $runScimBulk
    ) {}

    public function isConfigured() : bool
    {
        return $this->registerScimDirectory !== null
            && $this->readScimDirectories !== null
            && $this->rotateScimToken !== null
            && $this->markScimDirectoryOutage !== null
            && $this->recoverScimDirectoryOutage !== null
            && $this->provisionScimUser !== null
            && $this->deleteScimUser !== null
            && $this->readScimUsers !== null
            && $this->readScimGroups !== null
            && $this->syncScimGroups !== null
            && $this->runScimBulk !== null;
    }

    /**
     * @throws RandomException
     */
    public function registerDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->registerScimDirectoryOrFail()->execute(data: $data);
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

    public function markDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->markScimDirectoryOutageOrFail()->execute(data: $data);
    }

    private function markScimDirectoryOutageOrFail() : MarkScimDirectoryOutage
    {
        return $this->markScimDirectoryOutage ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'mark_directory_outage');
    }

    public function recoverDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->recoverScimDirectoryOutageOrFail()->execute(data: $data);
    }

    private function recoverScimDirectoryOutageOrFail() : RecoverScimDirectoryOutage
    {
        return $this->recoverScimDirectoryOutage ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'recover_directory_outage');
    }

    /**
     * @throws RandomException
     */
    public function provisionUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->provisionScimUserOrFail()->execute(data: $data);
    }

    private function provisionScimUserOrFail() : ProvisionScimUser
    {
        return $this->provisionScimUser ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'provision_user');
    }

    public function deleteUser(DeleteScimUserData $data) : void
    {
        $this->deleteScimUserOrFail()->execute(data: $data);
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
    public function syncGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->syncScimGroupsOrFail()->execute(data: $data);
    }

    private function syncScimGroupsOrFail() : SyncScimGroups
    {
        return $this->syncScimGroups ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'sync_groups');
    }

    public function runBulk(ScimBulkRequest $data) : ScimBulkResponse
    {
        return $this->runScimBulkOrFail()->execute(request: $data);
    }

    private function runScimBulkOrFail() : RunScimBulk
    {
        return $this->runScimBulk ?? throw IdentitySyncCapabilityUnavailable::scim(operation: 'run_bulk');
    }
}
