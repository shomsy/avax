<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ApproveChange;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\TenantSecurityFailed;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStatus;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStoreInterface;

final readonly class ApproveTenantSecurityChange
{
    public function __construct(private TenantSecurityChangeRequestStoreInterface $tenantSecurityChangeRequestStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws TenantSecurityFailed
     */
    public function execute(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        $changeRequest = $this->tenantSecurityChangeRequestStore->find(changeId: $changeId);

        if (! $changeRequest instanceof TenantSecurityChangeRequest) {
            throw TenantSecurityFailed::unknownChangeRequest();
        }

        $tenantSecurityChangeRequest = new TenantSecurityChangeRequest(
            changeId    : $changeRequest->changeId,
            tenantSlug  : $changeRequest->tenantSlug,
            requestedBy : $changeRequest->requestedBy,
            reason      : $changeRequest->reason,
            before      : $changeRequest->before,
            after       : $changeRequest->after,
            diff        : $changeRequest->diff,
            status      : TenantSecurityChangeRequestStatus::APPROVED,
            requestedAt : $changeRequest->requestedAt,
            approvedBy  : trim(string: $approvedBy),
            approvedAt  : $this->clock->now(),
            appliedAt   : $changeRequest->appliedAt,
            rolledBackAt: $changeRequest->rolledBackAt,
        );

        $this->tenantSecurityChangeRequestStore->save(changeRequest: $tenantSecurityChangeRequest);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant_security.change.approved',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'change_id'   => $tenantSecurityChangeRequest->changeId,
                                                           'tenant'      => $tenantSecurityChangeRequest->tenantSlug,
                                                           'approved_by' => $tenantSecurityChangeRequest->approvedBy,
                                                       ],
                                       ));

        return $tenantSecurityChangeRequest;
    }
}
