<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMemberState;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TenantFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class AcceptTenantInvite
{
    private Clock                $clock;
    private AuditLogInterface    $auditLog;
    private UserSourceInterface  $userSource;
    private TenantStoreInterface $tenantStore;

    public function __construct(
        TenantStoreInterface $tenantStore,
        UserSourceInterface  $userSource,
        AuditLogInterface    $auditLog,
        Clock                $clock
    )
    {
        $this->tenantStore = $tenantStore;
        $this->userSource  = $userSource;
        $this->auditLog    = $auditLog;
        $this->clock       = $clock;
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

        if (strtolower($user->getEmail()->value) !== strtolower($invite->email)) {
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
            joinedAt: $this->clock->now()
        );
        $this->tenantStore->saveMember(member: $member);
        $this->tenantStore->markInviteAccepted(
            inviteId        : $invite->inviteId,
            acceptedByUserId: $data->userId,
            acceptedAt      : $member->joinedAt
        );
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant.member.invite_accepted',
                                           occurredAt: $member->joinedAt,
                                           context   : [
                                                           'tenant_id' => $member->tenantId,
                                                           'user_id'   => $member->userId,
                                                           'role'      => $member->role->value,
                                                       ]
                                       ));

        return $member;
    }
}
