<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\RollbackChange;

use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\TenantSecurityFailed;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStatus;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfigurationStoreInterface;

final readonly class RollbackTenantSecurityChange
{
    public function __construct(private TenantSecurityConfigurationStoreInterface $tenantSecurityConfigurationStore, private TenantSecurityChangeRequestStoreInterface $tenantSecurityChangeRequestStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws TenantSecurityFailed
     */
    public function execute(string $changeId) : TenantSecurityConfiguration
    {
        $changeRequest = $this->tenantSecurityChangeRequestStore->find(changeId: $changeId);

        if (! $changeRequest instanceof TenantSecurityChangeRequest) {
            throw TenantSecurityFailed::unknownChangeRequest();
        }

        $rollback = $changeRequest->before ?? new TenantSecurityConfiguration(tenantSlug: $changeRequest->tenantSlug);
        $this->tenantSecurityConfigurationStore->save(configuration: $rollback);
        $tenantSecurityChangeRequest = new TenantSecurityChangeRequest(
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
            rolledBackAt: $this->clock->now(),
        );
        $this->tenantSecurityChangeRequestStore->save(changeRequest: $tenantSecurityChangeRequest);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant_security.change.rolled_back',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'change_id' => $tenantSecurityChangeRequest->changeId,
                                                           'tenant'    => $tenantSecurityChangeRequest->tenantSlug,
                                                       ],
                                       ));

        return $rollback;
    }
}
