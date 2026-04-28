<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model;

use DateTimeImmutable;
use SensitiveParameter;

interface TenantStoreInterface
{
    public function saveTenant(Tenant $tenant) : void;

    public function findTenantBySlug(string $slug) : Tenant|null;

    /**
     * @return list<Tenant>
     */
    public function allTenants() : array;

    public function saveMember(TenantMember $member) : void;

    public function findMember(string $tenantId, int $userId) : TenantMember|null;

    /**
     * @return list<TenantMember>
     */
    public function allMembers(string $tenantId) : array;

    public function removeMember(string $tenantId, int $userId) : void;

    public function saveInvite(TenantInvite $invite) : void;

    public function findInviteById(string $inviteId) : TenantInvite|null;

    public function findInviteByToken(#[SensitiveParameter] string $plainToken) : TenantInvite|null;

    public function markInviteAccepted(string $inviteId, int $acceptedByUserId, DateTimeImmutable $acceptedAt) : void;
}
