<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ApplyChange;

use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\TenantSecurityFailed;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStatus;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfigurationStoreInterface;

final readonly class ApplyTenantSecurityChange
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

        if ($changeRequest->status !== TenantSecurityChangeRequestStatus::APPROVED) {
            throw TenantSecurityFailed::approvalRequired();
        }

        $this->tenantSecurityConfigurationStore->save(configuration: $changeRequest->after);
        $tenantSecurityChangeRequest = new TenantSecurityChangeRequest(
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
            rolledBackAt: $changeRequest->rolledBackAt,
        );
        $this->tenantSecurityChangeRequestStore->save(changeRequest: $tenantSecurityChangeRequest);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant_security.change.applied',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'change_id'       => $tenantSecurityChangeRequest->changeId,
                                                           'tenant'          => $tenantSecurityChangeRequest->tenantSlug,
                                                           'rollout_version' => $tenantSecurityChangeRequest->after->rolloutVersion,
                                                       ],
                                       ));

        return $tenantSecurityChangeRequest->after;
    }
}
