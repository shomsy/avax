<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
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
    public function execute(InviteTenantMemberData $data): IssuedTenantInvite
    {
        $tenant = $this->tenantStore->findTenantBySlug(slug: $data->tenantSlug);

        if ($tenant === null) {
            throw TenantFailed::tenantNotFound(tenantSlug: $data->tenantSlug);
        }

        $plainTextToken = bin2hex(string: random_bytes(length: 32));
        $invite = new TenantInvite(
            inviteId : 'invite_' . bin2hex(string: random_bytes(length: 12)),
            tenantId : $tenant->tenantId,
            email    : strtolower(string: trim(string: $data->email)),
            role     : $data->role,
            tokenHash: hash(algo: 'sha256', data: $plainTextToken),
            invitedBy: trim(string: $data->invitedBy),
            createdAt: $this->clock->now(),
        );
        $this->tenantStore->saveInvite(invite: $invite);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant.member.invited',
            occurredAt: $invite->createdAt,
            context   : [
                            'tenant_id'  => $tenant->tenantId,
                'tenant_slug' => $tenant->slug,
                            'invite_id'  => $invite->inviteId,
                            'email'      => $invite->email,
                            'role'       => $invite->role->value,
                            'invited_by' => $invite->invitedBy,
            ],
        ));

        return new IssuedTenantInvite(invite: $invite, plainTextToken: $plainTextToken);
    }
}
