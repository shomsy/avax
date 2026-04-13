<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\RemoveMember;

use Avax\Auth\System\Capability\TenantMembership\Tenant;
use Avax\Auth\System\Capability\TenantMembership\Membership;
use Avax\Auth\System\Capability\TenantMembership\TenantMembershipStoreInterface;
use Avax\Auth\System\Capability\User\UserStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Orchestrator for removing a member from a tenant.
 *
 * Banal: The Tenant Remove Member file.
 */
final readonly class RemoveTenantMember
{
    public function __construct(
        private TenantMembershipStoreInterface $tenantMembershipStore,
        private UserStoreInterface $userStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Removes a member from a tenant.
     *
     * @throws \InvalidArgumentException
     */
    public function execute(string $userId, string $tenantId) : void
    {
        // Validate that the tenant exists and is active
        $tenant = $this->tenantMembershipStore->findTenant($tenantId);
        if ($tenant === null) {
            throw new \InvalidArgumentException("Tenant not found: {$tenantId}");
        }
        
        if (!$tenant->isActive()) {
            throw new \InvalidArgumentException("Tenant is not active: {$tenantId}");
        }

        // Validate that the user exists
        $user = $this->userStore->findById($userId);
        if ($user === null) {
            throw new \InvalidArgumentException("User not found: {$userId}");
        }

        // Find the membership
        $membership = $this->tenantMembershipStore->findMembershipByUserAndTenant(
            $userId,
            $tenantId
        );
        
        if ($membership === null) {
            throw new \InvalidArgumentException("Membership not found for user {$userId} in tenant {$tenantId}");
        }

        if (!$membership->isActive()) {
            throw new \InvalidArgumentException("Membership is already inactive for user {$userId} in tenant {$tenantId}");
        }

        // Leave the membership (soft delete)
        $leftMembership = $membership->leave();
        $this->tenantMembershipStore->saveMembership($leftMembership);

        // Audit the removal
        $this->auditLog->record(new AuditEvent(
            name: 'auth.tenant.member.removed',
            occurredAt: $this->clock->now(),
            context: [
                'tenant_id' => $tenantId,
                'tenant_name' => $tenant->getName(),
                'user_id' => $userId,
                'membership_id' => $membership->getMembershipId(),
                'role_id' => $membership->getRoleId(),
            ]
        ));
    }
}