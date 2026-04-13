<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\ApplyChange;

use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStatus;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStoreInterface;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfigurationStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\TenantSecurity\TenantSecurityFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class ApplyTenantSecurityChange
{
    public function __construct(
        private TenantSecurityConfigurationStoreInterface $configurationStore,
        private TenantSecurityChangeRequestStoreInterface $changeRequestStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws TenantSecurityFailed
     */
    public function execute(string $changeId) : TenantSecurityConfiguration
    {
        $changeRequest = $this->changeRequestStore->find(changeId: $changeId);

        if ($changeRequest === null) {
            throw TenantSecurityFailed::unknownChangeRequest();
        }

        if ($changeRequest->status !== TenantSecurityChangeRequestStatus::APPROVED) {
            throw TenantSecurityFailed::approvalRequired();
        }

        $this->configurationStore->save(configuration: $changeRequest->after);
        $applied = new TenantSecurityChangeRequest(
            changeId     : $changeRequest->changeId,
            tenantSlug   : $changeRequest->tenantSlug,
            requestedBy  : $changeRequest->requestedBy,
            reason       : $changeRequest->reason,
            before       : $changeRequest->before,
            after        : $changeRequest->after,
            diff         : $changeRequest->diff,
            status       : TenantSecurityChangeRequestStatus::APPLIED,
            requestedAt  : $changeRequest->requestedAt,
            approvedBy   : $changeRequest->approvedBy,
            approvedAt   : $changeRequest->approvedAt,
            appliedAt    : $this->clock->now(),
            rolledBackAt : $changeRequest->rolledBackAt
        );
        $this->changeRequestStore->save(changeRequest: $applied);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant_security.change.applied',
            occurredAt: $this->clock->now(),
            context   : [
                'change_id' => $applied->changeId,
                'tenant' => $applied->tenantSlug,
                'rollout_version' => $applied->after->rolloutVersion,
            ]
        ));

        return $applied->after;
    }
}
