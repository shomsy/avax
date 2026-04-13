<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\RollbackChange;

use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStatus;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStoreInterface;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfigurationStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\TenantSecurity\TenantSecurityFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class RollbackTenantSecurityChange
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
        $changeRequest = $this->changeRequestStore->find($changeId);

        if ($changeRequest === null) {
            throw TenantSecurityFailed::unknownChangeRequest();
        }

        $rollback = $changeRequest->before ?? new TenantSecurityConfiguration($changeRequest->tenantSlug);
        $this->configurationStore->save($rollback);
        $rolledBack = new TenantSecurityChangeRequest(
            changeId     : $changeRequest->changeId,
            tenantSlug   : $changeRequest->tenantSlug,
            requestedBy  : $changeRequest->requestedBy,
            reason       : $changeRequest->reason,
            before       : $changeRequest->before,
            after        : $changeRequest->after,
            diff         : $changeRequest->diff,
            status       : TenantSecurityChangeRequestStatus::ROLLED_BACK,
            requestedAt  : $changeRequest->requestedAt,
            approvedBy   : $changeRequest->approvedBy,
            approvedAt   : $changeRequest->approvedAt,
            appliedAt    : $changeRequest->appliedAt,
            rolledBackAt : $this->clock->now()
        );
        $this->changeRequestStore->save($rolledBack);
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.tenant_security.change.rolled_back',
            occurredAt: $this->clock->now(),
            context   : [
                'change_id' => $rolledBack->changeId,
                'tenant' => $rolledBack->tenantSlug,
            ]
        ));

        return $rollback;
    }
}
