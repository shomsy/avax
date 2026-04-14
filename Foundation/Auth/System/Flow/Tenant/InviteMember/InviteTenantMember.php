<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\InviteMember;

use Avax\Auth\System\Capability\Tenant\TenantInvite;
use Avax\Auth\System\Capability\Tenant\TenantStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Tenant\TenantFailed;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;

final readonly class InviteTenantMember
{
    public function __construct(
        private TenantStoreInterface $tenantStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws RandomException
     */
    public function execute(InviteTenantMemberData $data) : IssuedTenantInvite
    {
        $tenant = $this->tenantStore->findTenantBySlug(slug: $data->tenantSlug);

        if ($tenant === null) {
            throw TenantFailed::tenantNotFound(tenantSlug: $data->tenantSlug);
        }

        $plainTextToken = bin2hex(random_bytes(32));
        $invite = new TenantInvite(
            inviteId   : 'invite_' . bin2hex(random_bytes(12)),
            tenantId   : $tenant->tenantId,
            email      : strtolower(trim($data->email)),
            role       : $data->role,
            tokenHash  : hash('sha256', $plainTextToken),
            invitedBy  : trim($data->invitedBy),
            createdAt  : $this->clock->now()
        );
        $this->tenantStore->saveInvite(invite: $invite);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant.member.invited',
            occurredAt: $invite->createdAt,
            context   : [
                'tenant_id' => $tenant->tenantId,
                'tenant_slug' => $tenant->slug,
                'invite_id' => $invite->inviteId,
                'email' => $invite->email,
                'role' => $invite->role->value,
                'invited_by' => $invite->invitedBy,
            ]
        ));

        return new IssuedTenantInvite(invite: $invite, plainTextToken: $plainTextToken);
    }
}
