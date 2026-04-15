<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\TransferOwnership;

use Avax\Auth\System\Capability\Tenant\Tenant;
use Avax\Auth\System\Capability\Tenant\TenantMember;
use Avax\Auth\System\Capability\Tenant\TenantMemberRole;
use Avax\Auth\System\Capability\Tenant\TenantMemberState;
use Avax\Auth\System\Capability\Tenant\TenantStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Tenant\TenantFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class TransferTenantOwnership
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
                                                       joinedAt: $currentOwner->joinedAt
                                                   ));
        }

        $this->tenantStore->saveMember(member: new TenantMember(
                                                   tenantId: $nextOwner->tenantId,
                                                   userId  : $nextOwner->userId,
                                                   role    : TenantMemberRole::OWNER,
                                                   state   : TenantMemberState::ACTIVE,
                                                   joinedAt: $nextOwner->joinedAt
                                               ));

        $updated = new Tenant(
            tenantId   : $tenant->tenantId,
            slug       : $tenant->slug,
            name       : $tenant->name,
            ownerUserId: $data->newOwnerUserId,
            createdAt  : $tenant->createdAt
        );
        $this->tenantStore->saveTenant(tenant: $updated);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.owner.transferred',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'tenant_id'         => $tenant->tenantId,
                                                           'tenant_slug'       => $tenant->slug,
                                                           'new_owner_user_id' => $data->newOwnerUserId,
                                                       ]
                                       ));

        return $updated;
    }
}
