<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync;

use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\DeprovisionUser\DeprovisionUser;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser\ReactivateUser;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser\SuspendUser;
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
use RuntimeException;
use SensitiveParameter;

final readonly class IdentitySyncFacade
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
        private RunScimBulk|null                           $runScimBulk,
        private SuspendUser|null                           $suspendUser,
        private ReactivateUser|null                        $reactivateUser,
        private DeprovisionUser|null          $deprovisionUser
    ) {}

    /**
     * @throws RandomException
     */
    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->registerScimDirectoryOrFail()->execute(data: $data);
    }

    /**
     * @return list<ScimDirectory>
     */
    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->readScimDirectoriesOrFail()->execute(tenantSlug: $tenantSlug);
    }

    /**
     * @throws RandomException
     */
    public function rotateScimToken(string $directoryId) : RotatedScimToken
    {
        return $this->rotateScimTokenOrFail()->execute(directoryId: $directoryId);
    }

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->markScimDirectoryOutageOrFail()->execute(data: $data);
    }

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->recoverScimDirectoryOutageOrFail()->execute(data: $data);
    }

    /**
     * @throws RandomException
     */
    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->provisionScimUserOrFail()->execute(data: $data);
    }

    public function deleteScimUser(DeleteScimUserData $data) : void
    {
        $this->deleteScimUserOrFail()->execute(data: $data);
    }

    /**
     * @return list<ScimUserProjection>
     */
    public function readScimUsers(string $directoryId) : array
    {
        return $this->readScimUsersOrFail()->execute(directoryId: $directoryId);
    }

    /**
     * @return list<ScimGroupProjection>
     */
    public function readScimGroups(string $directoryId) : array
    {
        return $this->readScimGroupsOrFail()->execute(directoryId: $directoryId);
    }

    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->syncScimGroupsOrFail()->execute(data: $data);
    }

    public function runScimBulk(ScimBulkRequest $data) : ScimBulkResponse
    {
        return $this->runScimBulkOrFail()->execute(request: $data);
    }

    public function suspendUser(int $userId) : void
    {
        $this->suspendUserOrFail()->execute(userId: $userId);
    }

    public function reactivateUser(int $userId) : void
    {
        $this->reactivateUserOrFail()->execute(userId: $userId);
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->deprovisionUserOrFail()->execute(userId: $userId);
    }

    private function registerScimDirectoryOrFail() : RegisterScimDirectory
    {
        return $this->registerScimDirectory ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function readScimDirectoriesOrFail() : ReadScimDirectories
    {
        return $this->readScimDirectories ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function rotateScimTokenOrFail() : RotateScimToken
    {
        return $this->rotateScimToken ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function markScimDirectoryOutageOrFail() : MarkScimDirectoryOutage
    {
        return $this->markScimDirectoryOutage ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function recoverScimDirectoryOutageOrFail() : RecoverScimDirectoryOutage
    {
        return $this->recoverScimDirectoryOutage ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function provisionScimUserOrFail() : ProvisionScimUser
    {
        return $this->provisionScimUser ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function deleteScimUserOrFail() : DeleteScimUser
    {
        return $this->deleteScimUser ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function readScimUsersOrFail() : ReadScimUsers
    {
        return $this->readScimUsers ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function readScimGroupsOrFail() : ReadScimGroups
    {
        return $this->readScimGroups ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function syncScimGroupsOrFail() : SyncScimGroups
    {
        return $this->syncScimGroups ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function runScimBulkOrFail() : RunScimBulk
    {
        return $this->runScimBulk ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function suspendUserOrFail() : SuspendUser
    {
        return $this->suspendUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    private function reactivateUserOrFail() : ReactivateUser
    {
        return $this->reactivateUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    private function deprovisionUserOrFail() : DeprovisionUser
    {
        return $this->deprovisionUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }
}
