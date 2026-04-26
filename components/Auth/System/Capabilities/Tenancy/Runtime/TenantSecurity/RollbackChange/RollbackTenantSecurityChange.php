<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\RollbackChange;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\TenantSecurityFailed;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStatus;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfigurationStoreInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class RollbackTenantSecurityChange
{
    public function __construct(private TenantSecurityConfigurationStoreInterface $configurationStore, private TenantSecurityChangeRequestStoreInterface $changeRequestStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws TenantSecurityFailed
     */
    public function execute(string $changeId) : TenantSecurityConfiguration
    {
        $changeRequest = $this->changeRequestStore->find(changeId: $changeId);

        if ($changeRequest === null) {
            throw TenantSecurityFailed::unknownChangeRequest();
        }

        $rollback = $changeRequest->before ?? new TenantSecurityConfiguration(tenantSlug: $changeRequest->tenantSlug);
        $this->configurationStore->save(configuration: $rollback);
        $rolledBack = new TenantSecurityChangeRequest(
            changeId    : $changeRequest->changeId,
            tenantSlug  : $changeRequest->tenantSlug,
            requestedBy : $changeRequest->requestedBy,
            reason      : $changeRequest->reason,
            before      : $changeRequest->before,
            after       : $changeRequest->after,
            diff        : $changeRequest->diff,
            status      : TenantSecurityChangeRequestStatus::ROLLED_BACK,
            requestedAt : $changeRequest->requestedAt,
            approvedBy  : $changeRequest->approvedBy,
            approvedAt  : $changeRequest->approvedAt,
            appliedAt   : $changeRequest->appliedAt,
            rolledBackAt: $this->clock->now()
        );
        $this->changeRequestStore->save(changeRequest: $rolledBack);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant_security.change.rolled_back',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'change_id' => $rolledBack->changeId,
                                                           'tenant'    => $rolledBack->tenantSlug,
                                                       ]
                                       ));

        return $rollback;
    }
}
