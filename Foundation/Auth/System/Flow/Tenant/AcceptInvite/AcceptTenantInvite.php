<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\AcceptInvite;

use Avax\Auth\System\Capability\Tenant\TenantMember;
use Avax\Auth\System\Capability\Tenant\TenantMemberState;
use Avax\Auth\System\Capability\Tenant\TenantStoreInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Tenant\TenantFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class AcceptTenantInvite
{
    public function __construct(
        private TenantStoreInterface $tenantStore,
        private UserSourceInterface $userSource,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    public function execute(AcceptTenantInviteData $data) : TenantMember
    {
        $invite = $this->tenantStore->findInviteByToken(plainToken: $data->inviteToken);

        if ($invite === null || $invite->isAccepted()) {
            throw TenantFailed::inviteNotFound();
        }

        $user = $this->userSource->findById(id: new UserId(value: $data->userId));

        if ($user === null) {
            throw TenantFailed::userNotFound(userId: $data->userId);
        }

        if (strtolower($user->getEmail()->value) !== strtolower($invite->email)) {
            throw TenantFailed::inviteEmailMismatch();
        }

        if ($this->tenantStore->findMember(tenantId: $invite->tenantId, userId: $data->userId) !== null) {
            throw TenantFailed::memberAlreadyExists();
        }

        $member = new TenantMember(
            tenantId : $invite->tenantId,
            userId   : $data->userId,
            role     : $invite->role,
            state    : TenantMemberState::ACTIVE,
            joinedAt : $this->clock->now()
        );
        $this->tenantStore->saveMember(member: $member);
        $this->tenantStore->markInviteAccepted(
            inviteId         : $invite->inviteId,
            acceptedByUserId : $data->userId,
            acceptedAt       : $member->joinedAt
        );
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant.member.invite_accepted',
            occurredAt: $member->joinedAt,
            context   : [
                'tenant_id' => $member->tenantId,
                'user_id' => $member->userId,
                'role' => $member->role->value,
            ]
        ));

        return $member;
    }
}
