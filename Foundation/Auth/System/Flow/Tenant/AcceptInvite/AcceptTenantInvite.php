<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\AcceptInvite;

use Avax\Auth\System\Capability\TenantMembership\Tenant;
use Avax\Auth\System\Capability\TenantMembership\Membership;
use Avax\Auth\System\Capability\TenantMembership\MembershipInvite;
use Avax\Auth\System\Capability\TenantMembership\TenantMembershipStoreInterface;
use Avax\Auth\System\Capability\User\UserStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Orchestrator for accepting a tenant invitation.
 *
 * Banal: The Tenant Accept Invite file.
 */
final readonly class AcceptTenantInvite
{
    public function __construct(
        private TenantMembershipStoreInterface $tenantMembershipStore,
        private UserStoreInterface $userStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Accepts a tenant invitation and creates a membership.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function execute(string $inviteId, string $userId) : Membership
    {
        // Find the invitation
        $invite = $this->tenantMembershipStore->findInvite($inviteId);
        
        if ($invite === null) {
            throw new \InvalidArgumentException("Invitation not found: {$inviteId}");
        }

        // Validate that the invitation hasn't expired
        if ($invite->isExpired()) {
            throw new \InvalidArgumentException("Invitation has expired: {$inviteId}");
        }

        // Validate that the invitation hasn't already been accepted
        if ($invite->isAccepted()) {
            throw new \InvalidArgumentException("Invitation has already been accepted: {$inviteId}");
        }

        // Validate that the user ID matches the invited user
        if ($invite->getInvitedUserId() !== $userId) {
            throw new \InvalidArgumentException("User ID does not match invited user for invitation: {$inviteId}");
        }

        // Validate that the user exists
        $user = $this->userStore->findById($userId);
        if ($user === null) {
            throw new \InvalidArgumentException("User not found: {$userId}");
        }

        // Validate that the tenant exists and is active
        $tenant = $this->tenantMembershipStore->findTenant($invite->getTenantId());
        if ($tenant === null) {
            throw new \InvalidArgumentException("Tenant not found: {$invite->getTenantId()}");
        }
        
        if (!$tenant->isActive()) {
            throw new \InvalidArgumentException("Tenant is not active: {$invite->getTenantId()}");
        }

        // Validate that the role exists and belongs to tenant
        $role = $this->tenantMembershipStore->findRole($invite->getRoleId());
        if ($role === null) {
            throw new \InvalidArgumentException("Role not found: {$invite->getRoleId()}");
        }
        
        if ($role->getTenantId() !== $invite->getTenantId()) {
            throw new \InvalidArgumentException("Role does not belong to tenant: {$invite->getRoleId()}");
        }

        // Check if user is already an active member of the tenant
        $existingMembership = $this->tenantMembershipStore->findMembershipByUserAndTenant(
            $userId,
            $invite->getTenantId()
        );
        
        if ($existingMembership !== null && $existingMembership->isActive()) {
            throw new \InvalidArgumentException("User is already an active member of the tenant");
        }

        // Accept the invitation
        $acceptedInvite = $invite->accept();
        $this->tenantMembershipStore->saveInvite($acceptedInvite);

        // Create the membership
        $membership = new Membership(
            membershipId: uniqid('mem_', true),
            userId: $userId,
            tenantId: $invite->getTenantId(),
            roleId: $invite->getRoleId(),
            joinedAt: $this->clock->now(),
            leftAt: null,
            isActive: true
        );

        // Save the membership
        $this->tenantMembershipStore->saveMembership($membership);

        // Audit the acceptance
        $this->auditLog->record(new AuditEvent(
            name: 'auth.tenant.member.invite.accepted',
            occurredAt: $this->clock->now(),
            context: [
                'tenant_id' => $invite->getTenantId(),
                'tenant_name' => $tenant->getName(),
                'user_id' => $userId,
                'role_id' => $invite->getRoleId(),
                'invite_id' => $inviteId,
                'membership_id' => $membership->getMembershipId(),
            ]
        ));

        return $membership;
    }
}