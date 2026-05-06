<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Tenants;

use Avax\Components\Identity\Tenancy\System\Capabilities\Model\Tenant;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\AcceptInvite\AcceptTenantInvite;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\CreateTenant\CreateTenant;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\CreateTenant\CreateTenantData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember\InviteTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\ReadMembers\ReadTenantMembers;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\ReadTenants\ReadTenants;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\RemoveMember\RemoveTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\SuspendMember\SuspendTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TenantFailed;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TransferOwnership\TransferTenantOwnership;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Random\RandomException;

final readonly class Tenants
{
    public function __construct(
        private CreateTenant $createTenant,
        private ReadTenants $readTenants,
        private InviteTenantMember $inviteTenantMember,
        private AcceptTenantInvite $acceptTenantInvite,
        private ReadTenantMembers $readTenantMembers,
        private RemoveTenantMember $removeTenantMember,
        private SuspendTenantMember $suspendTenantMember,
        private TransferTenantOwnership $transferTenantOwnership,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function createTenant(CreateTenantData $createTenantData): Tenant
    {
        return $this->createTenant->execute(data: $createTenantData);
    }

    /**
     * @return list<Tenant>
     */
    public function readTenants(): array
    {
        return $this->readTenants->execute();
    }

    /**
     * @throws RandomException
     */
    public function inviteTenantMember(InviteTenantMemberData $inviteTenantMemberData): IssuedTenantInvite
    {
        return $this->inviteTenantMember->execute(data: $inviteTenantMemberData);
    }

    /**
     * @throws TenantFailed
     */
    public function acceptTenantInvite(AcceptTenantInviteData $acceptTenantInviteData): TenantMember
    {
        return $this->acceptTenantInvite->execute(data: $acceptTenantInviteData);
    }

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug): array
    {
        return $this->readTenantMembers->execute(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(RemoveTenantMemberData $removeTenantMemberData): void
    {
        $this->removeTenantMember->execute(data: $removeTenantMemberData);
    }

    public function suspendTenantMember(SuspendTenantMemberData $suspendTenantMemberData): TenantMember
    {
        return $this->suspendTenantMember->execute(data: $suspendTenantMemberData);
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $transferTenantOwnershipData): Tenant
    {
        return $this->transferTenantOwnership->execute(data: $transferTenantOwnershipData);
    }
}
