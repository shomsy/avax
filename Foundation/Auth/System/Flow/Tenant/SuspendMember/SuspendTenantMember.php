<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\SuspendMember;

use Avax\Auth\System\Capability\Tenant\TenantMember;
use Avax\Auth\System\Capability\Tenant\TenantMemberRole;
use Avax\Auth\System\Capability\Tenant\TenantMemberState;
use Avax\Auth\System\Capability\Tenant\TenantStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Tenant\TenantFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class SuspendTenantMember
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
            joinedAt: $member->joinedAt
        );
        $this->tenantStore->saveMember(member: $suspended);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.member.suspended',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'tenant_id'   => $tenant->tenantId,
                                                           'tenant_slug' => $tenant->slug,
                                                           'user_id'     => $member->userId,
                                                       ]
                                       ));

        return $suspended;
    }
}
