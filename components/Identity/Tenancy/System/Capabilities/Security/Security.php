<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Security;

use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ApplyChange\ApplyTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ApproveChange\ApproveTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadChangeRequest\ReadTenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadChangeRequests\ReadTenantSecurityChangeRequests;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadConfiguration\ReadTenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\RollbackChange\RollbackTenantSecurityChange;
use Random\RandomException;
use SensitiveParameter;

final readonly class Security
{
    public function __construct(
        #[SensitiveParameter]
        private ReadTenantSecurityConfiguration $readTenantSecurityConfiguration,
        #[SensitiveParameter]
        private ReadTenantSecurityChangeRequest $readTenantSecurityChangeRequest,
        #[SensitiveParameter]
        private ReadTenantSecurityChangeRequests $readTenantSecurityChangeRequests,
        #[SensitiveParameter]
        private BeginTenantSecurityChange $beginTenantSecurityChange,
        #[SensitiveParameter]
        private ApproveTenantSecurityChange $approveTenantSecurityChange,
        #[SensitiveParameter]
        private ApplyTenantSecurityChange $applyTenantSecurityChange,
        #[SensitiveParameter]
        private RollbackTenantSecurityChange $rollbackTenantSecurityChange,
    ) {}

    public function readConfiguration(string $tenantSlug): ?TenantSecurityConfiguration
    {
        return $this->readTenantSecurityConfiguration->execute(tenantSlug: $tenantSlug);
    }

    public function readChangeRequest(string $changeId): ?TenantSecurityChangeRequest
    {
        return $this->readTenantSecurityChangeRequest->execute(changeId: $changeId);
    }

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function readChangeRequests(string $tenantSlug): array
    {
        return $this->readTenantSecurityChangeRequests->execute(tenantSlug: $tenantSlug);
    }

    /**
     * @throws RandomException
     */
    public function beginChange(BeginTenantSecurityChangeData $beginTenantSecurityChangeData) : TenantSecurityChangeRequest
    {
        return $this->beginTenantSecurityChange->execute(data: $beginTenantSecurityChangeData);
    }

    public function approveChange(string $changeId, string $approvedBy): TenantSecurityChangeRequest
    {
        return $this->approveTenantSecurityChange->execute(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyChange(string $changeId): TenantSecurityConfiguration
    {
        return $this->applyTenantSecurityChange->execute(changeId: $changeId);
    }

    public function rollbackChange(string $changeId): TenantSecurityConfiguration
    {
        return $this->rollbackTenantSecurityChange->execute(changeId: $changeId);
    }
}
