<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync;

use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Random\RandomException;

final readonly class IdentitySync
{
    public function __construct(
        private SCIM\SCIM                 $scim,
        private Provisioning\Provisioning $provisioning
    ) {}

    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->scim->registerDirectory(data: $data);
    }

    /**
     * @return list<ScimDirectory>
     */
    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->scim->readDirectories(tenantSlug: $tenantSlug);
    }

    /**
     * @throws RandomException
     */
    public function rotateScimToken(string $directoryId) : RotatedScimToken
    {
        return $this->scim->rotateToken(directoryId: $directoryId);
    }

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->scim->markDirectoryOutage(data: $data);
    }

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->scim->recoverDirectoryOutage(data: $data);
    }

    /**
     * @throws RandomException
     */
    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->scim->provisionUser(data: $data);
    }

    public function deleteScimUser(DeleteScimUserData $data) : void
    {
        $this->scim->deleteUser(data: $data);
    }

    /**
     * @return list<ScimUserProjection>
     */
    public function readScimUsers(string $directoryId) : array
    {
        return $this->scim->readUsers(directoryId: $directoryId);
    }

    /**
     * @return list<ScimGroupProjection>
     */
    public function readScimGroups(string $directoryId) : array
    {
        return $this->scim->readGroups(directoryId: $directoryId);
    }

    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->scim->syncGroups(data: $data);
    }

    public function runScimBulk(ScimBulkRequest $data) : ScimBulkResponse
    {
        return $this->scim->runBulk(data: $data);
    }

    public function suspendUser(int $userId) : void
    {
        $this->provisioning->suspendUser(userId: $userId);
    }

    public function reactivateUser(int $userId) : void
    {
        $this->provisioning->reactivateUser(userId: $userId);
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->provisioning->deprovisionUser(userId: $userId);
    }
}
