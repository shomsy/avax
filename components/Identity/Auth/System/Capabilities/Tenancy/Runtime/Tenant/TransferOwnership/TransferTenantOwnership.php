<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMemberRole;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMemberState;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TenantFailed;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class TransferTenantOwnership
{
    public function __construct(private TenantStoreInterface $tenantStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(TransferTenantOwnershipData $data) : Tenant
    {
        $tenant = $this->tenantStore->findTenantBySlug(slug: $data->tenantSlug);

        if ($tenant === null) {
            throw TenantFailed::tenantNotFound(tenantSlug: $data->tenantSlug);
        }

        $currentOwner = $this->tenantStore->findMember(tenantId: $tenant->tenantId, userId: $tenant->ownerUserId);
        $nextOwner    = $this->tenantStore->findMember(tenantId: $tenant->tenantId, userId: $data->newOwnerUserId);

        if ($nextOwner === null) {
            throw TenantFailed::memberNotFound();
        }

        if ($currentOwner !== null) {
            $this->tenantStore->saveMember(member: new TenantMember(
                                                       tenantId: $currentOwner->tenantId,
                                                       userId  : $currentOwner->userId,
                                                       role    : TenantMemberRole::ADMIN,
                                                       state   : $currentOwner->state,
                                                       joinedAt: $currentOwner->joinedAt,
                                                   ));
        }

        $this->tenantStore->saveMember(member: new TenantMember(
                                                   tenantId: $nextOwner->tenantId,
                                                   userId  : $nextOwner->userId,
                                                   role    : TenantMemberRole::OWNER,
                                                   state   : TenantMemberState::ACTIVE,
                                                   joinedAt: $nextOwner->joinedAt,
                                               ));

        $updated = new Tenant(
            tenantId   : $tenant->tenantId,
            slug       : $tenant->slug,
            name       : $tenant->name,
            ownerUserId: $data->newOwnerUserId,
            createdAt  : $tenant->createdAt,
        );
        $this->tenantStore->saveTenant(tenant: $updated);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.owner.transferred',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'tenant_id'         => $tenant->tenantId,
                                                           'tenant_slug'       => $tenant->slug,
                                                           'new_owner_user_id' => $data->newOwnerUserId,
                                                       ],
                                       ));

        return $updated;
    }
}
