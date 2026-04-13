<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\ListMembers;

use Avax\Auth\System\Capability\TenantMembership\Tenant;
use Avax\Auth\System\Capability\TenantMembership\Membership;
use Avax\Auth\System\Capability\TenantMembership\TenantMembershipStoreInterface;
use Avax\Auth\System\Capability\User\UserStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Data transfer object for member information.
 */
final readonly class MemberInfo
{
    public function __construct(
        public readonly string $membershipId,
        public readonly string $userId,
        public readonly string $email,
        public readonly string|null $username,
        public readonly string $roleId,
        public readonly bool $isActive,
        public readonly string $joinedAt,
        public readonly string|null $leftAt
    ) {}
}

/**
 * Orchestrator for listing members of a tenant.
 *
 * Banal: The Tenant List Members file.
 */
final readonly class ListTenantMembers
{
    public function __construct(
        private TenantMembershipStoreInterface $tenantMembershipStore,
        private UserStoreInterface $userStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Lists all members of a tenant.
     *
     * @throws \InvalidArgumentException
     * @return list<MemberInfo>
     */
    public function execute(string $tenantId, bool $includeInactive = false) : array
    {
        // Validate that the tenant exists
        $tenant = $this->tenantMembershipStore->findTenant($tenantId);
        if ($tenant === null) {
            throw new \InvalidArgumentException("Tenant not found: {$tenantId}");
        }

        // Get all memberships for this tenant
        $memberships = $this->tenantMembershipStore->findMembershipsByTenant($tenantId);
        
        $members = [];
        
        foreach ($memberships as $membership) {
            // Skip inactive memberships unless requested
            if (!$includeInactive && !$membership->isActive()) {
                continue;
            }

            // Get user info
            $user = $this->userStore->findById($membership->getUserId());
            
            $members[] = new MemberInfo(
                membershipId: $membership->getMembershipId(),
                userId: $membership->getUserId(),
                email: $user?->getEmail()->value ?? 'unknown',
                username: $user?->getUsername() ?? null,
                roleId: $membership->getRoleId(),
                isActive: $membership->isActive(),
                joinedAt: $membership->getJoinedAt()->format('c'),
                leftAt: $membership->getLeftAt()?->format('c')
            );
        }

        // Audit the list retrieval
        $this->auditLog->record(new AuditEvent(
            name: 'auth.tenant.members.listed',
            occurredAt: $this->clock->now(),
            context: [
                'tenant_id' => $tenantId,
                'tenant_name' => $tenant->getName(),
                'member_count' => count($members),
                'include_inactive' => $includeInactive,
            ]
        ));

        return $members;
    }
}