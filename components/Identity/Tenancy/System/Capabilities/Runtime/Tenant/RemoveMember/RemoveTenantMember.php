<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\RemoveMember;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMemberRole;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TenantFailed;

final readonly class RemoveTenantMember
{
    public function __construct(private TenantStoreInterface $tenantStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(RemoveTenantMemberData $data): void
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
                'tenant_id' => $tenant->tenantId,
                'tenant_slug' => $tenant->slug,
                'user_id' => $data->userId,
            ],
        ));
    }
}
