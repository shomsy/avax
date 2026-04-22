<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMemberRole;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMemberState;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TenantFailed;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;

final readonly class CreateTenant
{
    public function __construct(private TenantStoreInterface $tenantStore, private UserSourceInterface $userSource, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    /**
     * @throws RandomException
     */
    public function execute(CreateTenantData $data) : Tenant
    {
        $slug = strtolower(trim($data->slug));

        if ($slug === '') {
            throw TenantFailed::tenantNotFound(tenantSlug: '');
        }

        if ($this->tenantStore->findTenantBySlug(slug: $slug) !== null) {
            throw TenantFailed::tenantSlugTaken(tenantSlug: $slug);
        }

        $owner = $this->userSource->findById(id: new UserId(value: $data->ownerUserId));

        if ($owner === null) {
            throw TenantFailed::userNotFound(userId: $data->ownerUserId);
        }

        $tenant = new Tenant(
            tenantId   : 'tenant_' . bin2hex(random_bytes(12)),
            slug       : $slug,
            name       : trim($data->name),
            ownerUserId: $data->ownerUserId,
            createdAt  : $this->clock->now()
        );
        $this->tenantStore->saveTenant(tenant: $tenant);
        $this->tenantStore->saveMember(member: new TenantMember(
                                                   tenantId: $tenant->tenantId,
                                                   userId  : $data->ownerUserId,
                                                   role    : TenantMemberRole::OWNER,
                                                   state   : TenantMemberState::ACTIVE,
                                                   joinedAt: $tenant->createdAt
                                               ));
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.created',
                                           occurredAt: $tenant->createdAt,
                                           context   : [
                                                           'tenant_id'     => $tenant->tenantId,
                                                           'tenant_slug'   => $tenant->slug,
                                                           'owner_user_id' => $tenant->ownerUserId,
                                                       ]
                                       ));

        return $tenant;
    }
}
