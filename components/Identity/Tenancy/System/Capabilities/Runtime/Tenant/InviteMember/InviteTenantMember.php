<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\Tenant;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantInvite;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TenantFailed;
use Random\RandomException;

final readonly class InviteTenantMember
{
    public function __construct(private TenantStoreInterface $tenantStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws RandomException
     */
    public function execute(InviteTenantMemberData $inviteTenantMemberData) : IssuedTenantInvite
    {
        $tenant       = $this->tenantStore->findTenantBySlug(slug: $inviteTenantMemberData->tenantSlug);

        if (! $tenant instanceof Tenant) {
            throw TenantFailed::tenantNotFound(tenantSlug: $inviteTenantMemberData->tenantSlug);
        }

        $plainTextToken = bin2hex(string: random_bytes(length: 32));
        $tenantInvite = new TenantInvite(
            inviteId : 'invite_' . bin2hex(string: random_bytes(length: 12)),
            tenantId : $tenant->tenantId,
            email    : strtolower(string: trim(string: $inviteTenantMemberData->email)),
            role     : $inviteTenantMemberData->role,
            tokenHash: hash(algo: 'sha256', data: $plainTextToken),
            invitedBy: trim(string: $inviteTenantMemberData->invitedBy),
            createdAt: $this->clock->now(),
        );
        $this->tenantStore->saveInvite(invite: $tenantInvite);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant.member.invited',
            occurredAt: $tenantInvite->createdAt,
            context   : [
                            'tenant_id'  => $tenant->tenantId,
                'tenant_slug' => $tenant->slug,
                            'invite_id'  => $tenantInvite->inviteId,
                            'email'      => $tenantInvite->email,
                            'role'       => $tenantInvite->role->value,
                            'invited_by' => $tenantInvite->invitedBy,
            ],
        ));

        return new IssuedTenantInvite(invite: $tenantInvite, plainTextToken: $plainTextToken);
    }
}
