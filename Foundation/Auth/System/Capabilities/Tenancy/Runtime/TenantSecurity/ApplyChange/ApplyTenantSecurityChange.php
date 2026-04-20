<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\TenantSecurity\ApplyChange;

use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequestStatus;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequestStoreInterface;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityConfigurationStoreInterface;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\TenantSecurity\TenantSecurityFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class ApplyTenantSecurityChange
{
    private Clock                                     $clock;
    private AuditLogInterface                         $auditLog;
    private TenantSecurityChangeRequestStoreInterface $changeRequestStore;
    private TenantSecurityConfigurationStoreInterface $configurationStore;

    public function __construct(
        TenantSecurityConfigurationStoreInterface $configurationStore,
        TenantSecurityChangeRequestStoreInterface $changeRequestStore,
        AuditLogInterface                         $auditLog,
        Clock                                     $clock
    )
    {
        $this->configurationStore = $configurationStore;
        $this->changeRequestStore = $changeRequestStore;
        $this->auditLog           = $auditLog;
        $this->clock              = $clock;
    }

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
            changeId    : $changeRequest->changeId,
            tenantSlug  : $changeRequest->tenantSlug,
            requestedBy : $changeRequest->requestedBy,
            reason      : $changeRequest->reason,
            before      : $changeRequest->before,
            after       : $changeRequest->after,
            diff        : $changeRequest->diff,
            status      : TenantSecurityChangeRequestStatus::APPLIED,
            requestedAt : $changeRequest->requestedAt,
            approvedBy  : $changeRequest->approvedBy,
            approvedAt  : $changeRequest->approvedAt,
            appliedAt   : $this->clock->now(),
            rolledBackAt: $changeRequest->rolledBackAt
        );
        $this->changeRequestStore->save(changeRequest: $applied);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant_security.change.applied',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'change_id'       => $applied->changeId,
                                                           'tenant'          => $applied->tenantSlug,
                                                           'rollout_version' => $applied->after->rolloutVersion,
                                                       ]
                                       ));

        return $applied->after;
    }
}
