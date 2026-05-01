<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\CreateTenant;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\Tenant;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMemberRole;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMemberState;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TenantFailed;
use Random\RandomException;

final readonly class CreateTenant
{
    public function __construct(private TenantStoreInterface $tenantStore, private UserSourceInterface $userSource, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws RandomException
     */
    public function execute(CreateTenantData $data): Tenant
    {
        $slug = strtolower(string: trim(string: $data->slug));

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
            tenantId   : 'tenant_'.bin2hex(string: random_bytes(length: 12)),
            slug       : $slug,
            name       : trim(string: $data->name),
            ownerUserId: $data->ownerUserId,
            createdAt  : $this->clock->now(),
        );
        $this->tenantStore->saveTenant(tenant: $tenant);
        $this->tenantStore->saveMember(member: new TenantMember(
            tenantId: $tenant->tenantId,
            userId  : $data->ownerUserId,
            role    : TenantMemberRole::OWNER,
            state   : TenantMemberState::ACTIVE,
            joinedAt: $tenant->createdAt,
        ));
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant.created',
            occurredAt: $tenant->createdAt,
            context   : [
                'tenant_id' => $tenant->tenantId,
                'tenant_slug' => $tenant->slug,
                'owner_user_id' => $tenant->ownerUserId,
            ],
        ));

        return $tenant;
    }
}
