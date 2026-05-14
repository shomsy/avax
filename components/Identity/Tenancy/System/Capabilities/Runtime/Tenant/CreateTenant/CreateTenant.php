<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\CreateTenant;

use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
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
    public function execute(CreateTenantData $createTenantData) : Tenant
    {
        $slug = strtolower(string: trim(string: $createTenantData->slug));

        if ($slug === '') {
            throw TenantFailed::tenantNotFound(tenantSlug: '');
        }

        if ($this->tenantStore->findTenantBySlug(slug: $slug) instanceof Tenant) {
            throw TenantFailed::tenantSlugTaken(tenantSlug: $slug);
        }

        $owner = $this->userSource->findById(id: new UserId(value: $createTenantData->ownerUserId));

        if (! $owner instanceof User) {
            throw TenantFailed::userNotFound(userId: $createTenantData->ownerUserId);
        }

        $tenant = new Tenant(
            tenantId   : 'tenant_' . bin2hex(string: random_bytes(length: 12)),
            slug       : $slug,
            name       : trim(string: $createTenantData->name),
            ownerUserId: $createTenantData->ownerUserId,
            createdAt  : $this->clock->now(),
        );
        $this->tenantStore->saveTenant(tenant: $tenant);
        $this->tenantStore->saveMember(member: new TenantMember(
                                                   tenantId: $tenant->tenantId,
                                                   userId  : $createTenantData->ownerUserId,
                                                   role    : TenantMemberRole::OWNER,
                                                   state   : TenantMemberState::ACTIVE,
                                                   joinedAt: $tenant->createdAt,
                                               ));
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.created',
                                           occurredAt: $tenant->createdAt,
                                           context   : [
                                                           'tenant_id'     => $tenant->tenantId,
                                                           'tenant_slug'   => $tenant->slug,
                                                           'owner_user_id' => $tenant->ownerUserId,
                                                       ],
                                       ));

        return $tenant;
    }
}
