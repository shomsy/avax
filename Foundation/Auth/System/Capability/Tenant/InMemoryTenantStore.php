<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Tenant;

use DateTimeImmutable;
use SensitiveParameter;

final class InMemoryTenantStore implements TenantStoreInterface
{
    /** @var array<string, Tenant> */
    private array $tenantsBySlug = [];

    /** @var array<string, TenantMember> */
    private array $members = [];

    /** @var array<string, TenantInvite> */
    private array $invites = [];

    public function saveTenant(Tenant $tenant) : void
    {
        $this->tenantsBySlug[strtolower($tenant->slug)] = $tenant;
    }

    public function findTenantBySlug(string $slug) : Tenant|null
    {
        return $this->tenantsBySlug[strtolower(trim($slug))] ?? null;
    }

    public function allTenants() : array
    {
        return array_values($this->tenantsBySlug);
    }

    public function saveMember(TenantMember $member) : void
    {
        $this->members[$this->memberKey(tenantId: $member->tenantId, userId: $member->userId)] = $member;
    }

    public function findMember(string $tenantId, int $userId) : TenantMember|null
    {
        return $this->members[$this->memberKey(tenantId: $tenantId, userId: $userId)] ?? null;
    }

    public function allMembers(string $tenantId) : array
    {
        return array_values(array_filter(
            $this->members,
            static fn (TenantMember $member) : bool => $member->tenantId === $tenantId
        ));
    }

    public function removeMember(string $tenantId, int $userId) : void
    {
        unset($this->members[$this->memberKey(tenantId: $tenantId, userId: $userId)]);
    }

    public function saveInvite(TenantInvite $invite) : void
    {
        $this->invites[$invite->inviteId] = $invite;
    }

    public function findInviteById(string $inviteId) : TenantInvite|null
    {
        return $this->invites[$inviteId] ?? null;
    }

    public function findInviteByToken(#[SensitiveParameter] string $plainToken) : TenantInvite|null
    {
        $tokenHash = hash('sha256', $plainToken);

        foreach ($this->invites as $invite) {
            if ($invite->isAccepted()) {
                continue;
            }

            if (hash_equals($invite->tokenHash, $tokenHash)) {
                return $invite;
            }
        }

        return null;
    }

    public function markInviteAccepted(string $inviteId, int $acceptedByUserId, DateTimeImmutable $acceptedAt) : void
    {
        $invite = $this->invites[$inviteId] ?? null;

        if ($invite === null) {
            return;
        }

        $this->invites[$inviteId] = new TenantInvite(
            inviteId         : $invite->inviteId,
            tenantId         : $invite->tenantId,
            email            : $invite->email,
            role             : $invite->role,
            tokenHash        : $invite->tokenHash,
            invitedBy        : $invite->invitedBy,
            createdAt        : $invite->createdAt,
            acceptedAt       : $acceptedAt,
            acceptedByUserId : $acceptedByUserId
        );
    }

    private function memberKey(string $tenantId, int $userId) : string
    {
        return $tenantId . ':' . $userId;
    }
}
