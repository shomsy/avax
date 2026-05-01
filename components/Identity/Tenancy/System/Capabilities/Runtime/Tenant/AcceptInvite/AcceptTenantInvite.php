<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\AcceptInvite;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantInvite;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMemberState;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TenantFailed;

final readonly class AcceptTenantInvite
{
    public function __construct(private TenantStoreInterface $tenantStore, private UserSourceInterface $userSource, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(AcceptTenantInviteData $acceptTenantInviteData) : TenantMember
    {
        $invite = $this->tenantStore->findInviteByToken(plainToken: $acceptTenantInviteData->inviteToken);

        if (! $invite instanceof TenantInvite || $invite->isAccepted()) {
            throw TenantFailed::inviteNotFound();
        }

        $user = $this->userSource->findById(id: new UserId(value: $acceptTenantInviteData->userId));

        if (! $user instanceof User) {
            throw TenantFailed::userNotFound(userId: $acceptTenantInviteData->userId);
        }

        if (strtolower(string: $user->getEmail()->value) !== strtolower(string: $invite->email)) {
            throw TenantFailed::inviteEmailMismatch();
        }

        if ($this->tenantStore->findMember(tenantId: $invite->tenantId, userId: $acceptTenantInviteData->userId) instanceof TenantMember) {
            throw TenantFailed::memberAlreadyExists();
        }

        $tenantMember = new TenantMember(
            tenantId: $invite->tenantId,
            userId  : $acceptTenantInviteData->userId,
            role    : $invite->role,
            state   : TenantMemberState::ACTIVE,
            joinedAt: $this->clock->now(),
        );
        $this->tenantStore->saveMember(member: $tenantMember);
        $this->tenantStore->markInviteAccepted(
            inviteId        : $invite->inviteId,
            acceptedByUserId: $acceptTenantInviteData->userId,
            acceptedAt      : $tenantMember->joinedAt,
        );
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant.member.invite_accepted',
            occurredAt: $tenantMember->joinedAt,
            context   : [
                            'tenant_id' => $tenantMember->tenantId,
                            'user_id'   => $tenantMember->userId,
                            'role'      => $tenantMember->role->value,
            ],
        ));

        return $tenantMember;
    }
}
