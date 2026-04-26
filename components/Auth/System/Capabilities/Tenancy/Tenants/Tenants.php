<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Tenants;

use Avax\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInvite;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenant;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenantData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\ReadMembers\ReadTenantMembers;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\ReadTenants\ReadTenants;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TenantFailed;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnership;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Random\RandomException;

final readonly class Tenants
{
    public function __construct(
        private CreateTenant            $createTenant,
        private ReadTenants             $readTenants,
        private InviteTenantMember      $inviteTenantMember,
        private AcceptTenantInvite      $acceptTenantInvite,
        private ReadTenantMembers       $readTenantMembers,
        private RemoveTenantMember      $removeTenantMember,
        private SuspendTenantMember     $suspendTenantMember,
        private TransferTenantOwnership $transferTenantOwnership
    ) {}

    /**
     * @throws RandomException
     */
    public function createTenant(CreateTenantData $data) : Tenant
    {
        return $this->createTenant->execute(data: $data);
    }

    /**
     * @return list<Tenant>
     */
    public function readTenants() : array
    {
        return $this->readTenants->execute();
    }

    /**
     * @throws RandomException
     */
    public function inviteTenantMember(InviteTenantMemberData $data) : IssuedTenantInvite
    {
        return $this->inviteTenantMember->execute(data: $data);
    }

    /**
     * @throws TenantFailed
     */
    public function acceptTenantInvite(AcceptTenantInviteData $data) : TenantMember
    {
        return $this->acceptTenantInvite->execute(data: $data);
    }

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug) : array
    {
        return $this->readTenantMembers->execute(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(RemoveTenantMemberData $data) : void
    {
        $this->removeTenantMember->execute(data: $data);
    }

    public function suspendTenantMember(SuspendTenantMemberData $data) : TenantMember
    {
        return $this->suspendTenantMember->execute(data: $data);
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $data) : Tenant
    {
        return $this->transferTenantOwnership->execute(data: $data);
    }
}
