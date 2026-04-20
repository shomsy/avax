<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Tenant\CreateTenant;

use Avax\Auth\System\Capabilities\Tenant\Tenant;
use Avax\Auth\System\Capabilities\Tenant\TenantMember;
use Avax\Auth\System\Capabilities\Tenant\TenantMemberRole;
use Avax\Auth\System\Capabilities\Tenant\TenantMemberState;
use Avax\Auth\System\Capabilities\Tenant\TenantStoreInterface;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Tenant\TenantFailed;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;

final readonly class CreateTenant
{
    private Clock                $clock;
    private AuditLogInterface    $auditLog;
    private UserSourceInterface  $userSource;
    private TenantStoreInterface $tenantStore;

    public function __construct(
        TenantStoreInterface $tenantStore,
        UserSourceInterface  $userSource,
        AuditLogInterface    $auditLog,
        Clock                $clock
    )
    {
        $this->tenantStore = $tenantStore;
        $this->userSource  = $userSource;
        $this->auditLog    = $auditLog;
        $this->clock       = $clock;
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
