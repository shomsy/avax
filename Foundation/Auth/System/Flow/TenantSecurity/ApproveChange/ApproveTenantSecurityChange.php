<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\ApproveChange;

use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStatus;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\TenantSecurity\TenantSecurityFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class ApproveTenantSecurityChange
{
    public function __construct(
        private TenantSecurityChangeRequestStoreInterface $changeRequestStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws TenantSecurityFailed
     */
    public function execute(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        $changeRequest = $this->changeRequestStore->find(changeId: $changeId);

        if ($changeRequest === null) {
            throw TenantSecurityFailed::unknownChangeRequest();
        }

        $approved = new TenantSecurityChangeRequest(
            changeId     : $changeRequest->changeId,
            tenantSlug   : $changeRequest->tenantSlug,
            requestedBy  : $changeRequest->requestedBy,
            reason       : $changeRequest->reason,
            before       : $changeRequest->before,
            after        : $changeRequest->after,
            diff         : $changeRequest->diff,
            status       : TenantSecurityChangeRequestStatus::APPROVED,
            requestedAt  : $changeRequest->requestedAt,
            approvedBy   : trim($approvedBy),
            approvedAt   : $this->clock->now(),
            appliedAt    : $changeRequest->appliedAt,
            rolledBackAt : $changeRequest->rolledBackAt
        );

        $this->changeRequestStore->save(changeRequest: $approved);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant_security.change.approved',
            occurredAt: $this->clock->now(),
            context   : [
                'change_id' => $approved->changeId,
                'tenant' => $approved->tenantSlug,
                'approved_by' => $approved->approvedBy,
            ]
        ));

        return $approved;
    }
}
