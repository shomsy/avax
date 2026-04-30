<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMemberRole;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMemberState;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TenantFailed;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class SuspendTenantMember
{
    public function __construct(private TenantStoreInterface $tenantStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(SuspendTenantMemberData $data) : TenantMember
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

        $suspended = new TenantMember(
            tenantId: $member->tenantId,
            userId  : $member->userId,
            role    : $member->role,
            state   : TenantMemberState::SUSPENDED,
            joinedAt: $member->joinedAt,
        );
        $this->tenantStore->saveMember(member: $suspended);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.member.suspended',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'tenant_id'   => $tenant->tenantId,
                                                           'tenant_slug' => $tenant->slug,
                                                           'user_id'     => $member->userId,
                                                       ],
                                       ));

        return $suspended;
    }
}
