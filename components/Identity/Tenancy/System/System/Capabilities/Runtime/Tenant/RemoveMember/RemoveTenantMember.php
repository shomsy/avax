<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\RemoveMember;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\Tenant;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantMember;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantMemberRole;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\TenantFailed;

final readonly class RemoveTenantMember
{
    public function __construct(private TenantStoreInterface $tenantStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(RemoveTenantMemberData $removeTenantMemberData) : void
    {
        $tenant = $this->tenantStore->findTenantBySlug(slug: $removeTenantMemberData->tenantSlug);

        if (! $tenant instanceof Tenant) {
            throw TenantFailed::tenantNotFound(tenantSlug: $removeTenantMemberData->tenantSlug);
        }

        $member = $this->tenantStore->findMember(tenantId: $tenant->tenantId, userId: $removeTenantMemberData->userId);

        if (! $member instanceof TenantMember) {
            throw TenantFailed::memberNotFound();
        }

        if ($member->role === TenantMemberRole::OWNER) {
            throw TenantFailed::ownerCannotBeRemoved();
        }

        $this->tenantStore->removeMember(tenantId: $tenant->tenantId, userId: $removeTenantMemberData->userId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.member.removed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'tenant_id'   => $tenant->tenantId,
                                                           'tenant_slug' => $tenant->slug,
                                                           'user_id'     => $removeTenantMemberData->userId,
                                                       ],
                                       ));
    }
}
