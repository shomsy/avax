<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ApplyChange;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\TenantSecurityFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStatus;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfigurationStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class ApplyTenantSecurityChange
{
    public function __construct(private TenantSecurityConfigurationStoreInterface $configurationStore, private TenantSecurityChangeRequestStoreInterface $changeRequestStore, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    /**
     * @throws TenantSecurityFailed
     */
    public function execute(string $changeId): TenantSecurityConfiguration
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
            rolledBackAt: $changeRequest->rolledBackAt,
        );
        $this->changeRequestStore->save(changeRequest: $applied);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant_security.change.applied',
            occurredAt: $this->clock->now(),
            context   : [
                                                           'change_id'       => $applied->changeId,
                                                           'tenant'          => $applied->tenantSlug,
                                                           'rollout_version' => $applied->after->rolloutVersion,
                                                       ],
        ));

        return $applied->after;
    }
}
