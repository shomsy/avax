<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy;

use Avax\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenantData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use SensitiveParameter;

final readonly class Tenancy
{
    public function __construct(
        private Tenants\Tenants                         $tenants,
        #[SensitiveParameter] private Security\Security $security
    ) {}

    public function createTenant(CreateTenantData $data) : Tenant
    {
        return $this->tenants->createTenant(data: $data);
    }

    /**
     * @return list<Tenant>
     */
    public function readTenants() : array
    {
        return $this->tenants->readTenants();
    }

    public function inviteTenantMember(InviteTenantMemberData $data) : IssuedTenantInvite
    {
        return $this->tenants->inviteTenantMember(data: $data);
    }

    public function acceptTenantInvite(AcceptTenantInviteData $data) : TenantMember
    {
        return $this->tenants->acceptTenantInvite(data: $data);
    }

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug) : array
    {
        return $this->tenants->readTenantMembers(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(RemoveTenantMemberData $data) : void
    {
        $this->tenants->removeTenantMember(data: $data);
    }

    public function suspendTenantMember(SuspendTenantMemberData $data) : TenantMember
    {
        return $this->tenants->suspendTenantMember(data: $data);
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $data) : Tenant
    {
        return $this->tenants->transferTenantOwnership(data: $data);
    }

    public function readTenantSecurityConfiguration(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->security->readConfiguration(tenantSlug: $tenantSlug);
    }

    public function readTenantSecurityChangeRequest(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->security->readChangeRequest(changeId: $changeId);
    }

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug) : array
    {
        return $this->security->readChangeRequests(tenantSlug: $tenantSlug);
    }

    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        return $this->security->beginChange(data: $data);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        return $this->security->approveChange(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->security->applyChange(changeId: $changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->security->rollbackChange(changeId: $changeId);
    }
}
