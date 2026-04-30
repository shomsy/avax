<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ApproveChange;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\TenantSecurityFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStatus;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class ApproveTenantSecurityChange
{
    public function __construct(private TenantSecurityChangeRequestStoreInterface $changeRequestStore, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    /**
     * @throws TenantSecurityFailed
     */
    public function execute(string $changeId, string $approvedBy): TenantSecurityChangeRequest
    {
        $changeRequest = $this->changeRequestStore->find(changeId: $changeId);

        if ($changeRequest === null) {
            throw TenantSecurityFailed::unknownChangeRequest();
        }

        $approved = new TenantSecurityChangeRequest(
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

        $this->changeRequestStore->save(changeRequest: $approved);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant_security.change.approved',
            occurredAt: $this->clock->now(),
            context   : [
                                                           'change_id'   => $approved->changeId,
                                                           'tenant'      => $approved->tenantSlug,
                                                           'approved_by' => $approved->approvedBy,
                                                       ],
        ));

        return $approved;
    }
}
