<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMemberRole;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TenantFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class RemoveTenantMember
{
    private Clock                $clock;
    private AuditLogInterface    $auditLog;
    private TenantStoreInterface $tenantStore;

    public function __construct(
        TenantStoreInterface $tenantStore,
        AuditLogInterface    $auditLog,
        Clock                $clock
    )
    {
        $this->tenantStore = $tenantStore;
        $this->auditLog    = $auditLog;
        $this->clock       = $clock;
    }

    public function execute(RemoveTenantMemberData $data) : void
    {
        $tenant = $this->tenantStore->findTenantBySlug(slug: $data->tenantSlug);

        if ($tenant === null) {
            throw TenantFailed::tenantNotFound(tenantSlug: $data->tenantSlug);
        }

        $member = $this->tenantStore->findMember(tenantId: $tenant->tenantId, userId: $data->userId);

        if ($member === null) {
            throw TenantFailed::memberNotFound();
        }

        if ($member->role === TenantMemberRole::OWNER) {
            throw TenantFailed::ownerCannotBeRemoved();
        }

        $this->tenantStore->removeMember(tenantId: $tenant->tenantId, userId: $data->userId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.member.removed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'tenant_id'   => $tenant->tenantId,
                                                           'tenant_slug' => $tenant->slug,
                                                           'user_id'     => $data->userId,
                                                       ]
                                       ));
    }
}
