<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Tenant;

use Avax\Auth\System\Capability\Tenant\Tenant;
use Avax\Auth\System\Capability\Tenant\TenantMember;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Flow\Tenant\AcceptInvite\AcceptTenantInvite;
use Avax\Auth\System\Flow\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Auth\System\Flow\Tenant\CreateTenant\CreateTenant;
use Avax\Auth\System\Flow\Tenant\CreateTenant\CreateTenantData;
use Avax\Auth\System\Flow\Tenant\InviteMember\InviteTenantMember;
use Avax\Auth\System\Flow\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Auth\System\Flow\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Auth\System\Flow\Tenant\ReadMembers\ReadTenantMembers;
use Avax\Auth\System\Flow\Tenant\ReadTenants\ReadTenants;
use Avax\Auth\System\Flow\Tenant\RemoveMember\RemoveTenantMember;
use Avax\Auth\System\Flow\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Auth\System\Flow\Tenant\SuspendMember\SuspendTenantMember;
use Avax\Auth\System\Flow\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Auth\System\Flow\Tenant\TransferOwnership\TransferTenantOwnership;
use Avax\Auth\System\Flow\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Auth\System\Flow\TenantSecurity\ApplyChange\ApplyTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\ApproveChange\ApproveTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequest\ReadTenantSecurityChangeRequest;
use Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequests\ReadTenantSecurityChangeRequests;
use Avax\Auth\System\Flow\TenantSecurity\ReadConfiguration\ReadTenantSecurityConfiguration;
use Avax\Auth\System\Flow\Tenant\TenantFailed;
use Avax\Auth\System\Flow\TenantSecurity\RollbackChange\RollbackTenantSecurityChange;
use Random\RandomException;
use RuntimeException;

final readonly class TenancyFacade
{
    public function __construct(
        private CreateTenant                     $createTenant,
        private ReadTenants                      $readTenants,
        private InviteTenantMember               $inviteTenantMember,
        private AcceptTenantInvite               $acceptTenantInvite,
        private ReadTenantMembers                $readTenantMembers,
        private RemoveTenantMember               $removeTenantMember,
        private SuspendTenantMember              $suspendTenantMember,
        private TransferTenantOwnership          $transferTenantOwnership,
        private ReadTenantSecurityConfiguration  $readTenantSecurityConfiguration,
        private ReadTenantSecurityChangeRequest  $readTenantSecurityChangeRequest,
        private ReadTenantSecurityChangeRequests $readTenantSecurityChangeRequests,
        private BeginTenantSecurityChange        $beginTenantSecurityChange,
        private ApproveTenantSecurityChange      $approveTenantSecurityChange,
        private ApplyTenantSecurityChange        $applyTenantSecurityChange,
        private RollbackTenantSecurityChange     $rollbackTenantSecurityChange
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

    public function readTenantSecurityConfiguration(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->readTenantSecurityConfiguration->execute(tenantSlug: $tenantSlug);
    }

    public function readTenantSecurityChangeRequest(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->readTenantSecurityChangeRequest->execute(changeId: $changeId);
    }

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug) : array
    {
        return $this->readTenantSecurityChangeRequests->execute(tenantSlug: $tenantSlug);
    }

    /**
     * @throws RandomException
     */
    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        return $this->beginTenantSecurityChange->execute(data: $data);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        return $this->approveTenantSecurityChange->execute(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->applyTenantSecurityChange->execute(changeId: $changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->rollbackTenantSecurityChange->execute(changeId: $changeId);
    }
}
