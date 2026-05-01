<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\AcceptInvite;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMemberState;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TenantFailed;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class AcceptTenantInvite
{
    public function __construct(private TenantStoreInterface $tenantStore, private UserSourceInterface $userSource, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

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

        if (strtolower(string: $user->getEmail()->value) !== strtolower(string: $invite->email)) {
            throw TenantFailed::inviteEmailMismatch();
        }

        if ($this->tenantStore->findMember(tenantId: $invite->tenantId, userId: $data->userId) !== null) {
            throw TenantFailed::memberAlreadyExists();
        }

        $member = new TenantMember(
            tenantId: $invite->tenantId,
            userId  : $data->userId,
            role    : $invite->role,
            state   : TenantMemberState::ACTIVE,
            joinedAt: $this->clock->now(),
        );
        $this->tenantStore->saveMember(member: $member);
        $this->tenantStore->markInviteAccepted(
            inviteId        : $invite->inviteId,
            acceptedByUserId: $data->userId,
            acceptedAt      : $member->joinedAt,
        );
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant.member.invite_accepted',
            occurredAt: $member->joinedAt,
            context   : [
                                                           'tenant_id' => $member->tenantId,
                                                           'user_id'   => $member->userId,
                                                           'role'      => $member->role->value,
                                                       ],
        ));

        return $member;
    }
}
