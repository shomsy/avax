<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant;

use Avax\Auth\System\Capability\TenantMembership\Tenant;
use Avax\Auth\System\Capability\TenantMembership\Membership;
use Avax\Auth\System\Capability\TenantMembership\MembershipInvite;
use Avax\Auth\System\Capability\TenantMembership\MembershipRole;
use Avax\Auth\System\Capability\TenantMembership\TenantMembershipStoreInterface;
use Avax\Auth\System\Capability\User\UserStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Orchestrates inviting a user to become a member of a tenant.
 *
 * Banal: The Tenant Invite Member file.
 */
final readonly class InviteMember
{
    public function __construct(
        private TenantMembershipStoreInterface $tenantMembershipStore,
        private UserStoreInterface $userStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Invites a user to join a tenant with a specific role.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function execute(
        string $tenantId,
        string $invitedByUserId,
        string $invitedUserEmail,
        string $roleId
    ) : MembershipInvite
    {
        // Validate tenant exists and is active
        $tenant = $this->tenantMembershipStore->findTenant($tenantId);
        if ($tenant === null) {
            throw new \InvalidArgumentException("Tenant not found: {$tenantId}");
        }
        
        if (!$tenant->isActive()) {
            throw new \InvalidArgumentException("Tenant is not active: {$tenantId}");
        }

        // Validate inviter exists
        $inviter = $this->userStore->findById($invitedByUserId);
        if ($inviter === null) {
            throw new \InvalidArgumentException("Inviter not found: {$invitedByUserId}");
        }

        // Validate user to invite exists (by email)
        $userToInvite = $this->userStore->findByEmail($invitedUserEmail);
        if ($userToInvite === null) {
            throw new \InvalidArgumentException("User to invite not found: {$invitedUserEmail}");
        }

        // Validate role exists and belongs to tenant
        $role = $this->tenantMembershipStore->findRole($roleId);
        if ($role === null) {
            throw new \InvalidArgumentException("Role not found: {$roleId}");
        }
        
        if ($role->getTenantId() !== $tenantId) {
            throw new \InvalidArgumentException("Role does not belong to tenant: {$roleId}");
        }

        // Check if user is already a member of the tenant
        $existingMembership = $this->tenantMembershipStore->findMembershipByUserAndTenant(
            $userToInvite->getId(),
            $tenantId
        );
        
        if ($existingMembership !== null && $existingMembership->isActive()) {
            throw new \InvalidArgumentException("User is already an active member of the tenant");
        }

        // Create the invitation
        $invite = new MembershipInvite(
            inviteId: uniqid('inv_', true),
            tenantId: $tenantId,
            invitedUserId: $userToInvite->getUserId()->value,
            invitedByUserId: $invitedByUserId,
            roleId: $roleId,
            createdAt: $this->clock->now(),
            expiresAt: (new \DateTimeImmutable())->modify('+7 days') // 7-day expiry
        );

        // Save the invitation
        $this->tenantMembershipStore->saveInvite($invite);

        // Audit the invitation
        $this->auditLog->record(new AuditEvent(
            name: 'auth.tenant.member.invited',
            occurredAt: $this->clock->now(),
            context: [
                'tenant_id' => $tenantId,
                'tenant_name' => $tenant->getName(),
                'invited_user_id' => $userToInvite->getId(),
                'invited_by_user_id' => $invitedByUserId,
                'role_id' => $roleId,
                'invite_id' => $invite->getInviteId(),
                'expires_at' => $invite->getExpiresAt()?->format('c')
            ]
        ));

        return $invite;
    }
}