<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\TransferOwnership;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\Tenant;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantMember;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantMemberRole;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantMemberState;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\TenantFailed;

final readonly class TransferTenantOwnership
{
    public function __construct(private TenantStoreInterface $tenantStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(TransferTenantOwnershipData $transferTenantOwnershipData) : Tenant
    {
        $tenant = $this->tenantStore->findTenantBySlug(slug: $transferTenantOwnershipData->tenantSlug);

        if (! $tenant instanceof Tenant) {
            throw TenantFailed::tenantNotFound(tenantSlug: $transferTenantOwnershipData->tenantSlug);
        }

        $currentOwner = $this->tenantStore->findMember(tenantId: $tenant->tenantId, userId: $tenant->ownerUserId);
        $nextOwner    = $this->tenantStore->findMember(tenantId: $tenant->tenantId, userId: $transferTenantOwnershipData->newOwnerUserId);

        if (! $nextOwner instanceof TenantMember) {
            throw TenantFailed::memberNotFound();
        }

        if ($currentOwner instanceof TenantMember) {
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
            ownerUserId: $transferTenantOwnershipData->newOwnerUserId,
            createdAt  : $tenant->createdAt,
        );
        $this->tenantStore->saveTenant(tenant: $updated);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.owner.transferred',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'tenant_id'         => $tenant->tenantId,
                                                           'tenant_slug'       => $tenant->slug,
                                                           'new_owner_user_id' => $transferTenantOwnershipData->newOwnerUserId,
                                                       ],
                                       ));

        return $updated;
    }
}
