<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantMembership;

/**
 * Interface for tenant membership storage operations.
 */
interface TenantMembershipStoreInterface
{
    public function findTenant(string $tenantId) : ?Tenant;
    public function saveTenant(Tenant $tenant) : void;
    
    public function findRole(string $roleId) : ?MembershipRole;
    public function saveRole(MembershipRole $role) : void;
    
    public function findMembershipByUserAndTenant(string $userId, string $tenantId) : ?Membership;
    public function findMembershipsByTenant(string $tenantId) : array;
    public function saveMembership(Membership $membership) : void;
    
    public function saveInvite(MembershipInvite $invite) : void;
    public function findInvite(string $inviteId) : ?MembershipInvite;
}