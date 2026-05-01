<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Model;

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

    public function saveTenant(Tenant $tenant): void
    {
        $this->tenantsBySlug[strtolower(string: $tenant->slug)] = $tenant;
    }

    public function findTenantBySlug(string $slug): ?Tenant
    {
        return $this->tenantsBySlug[strtolower(string: trim(string: $slug))] ?? null;
    }

    public function allTenants(): array
    {
        return array_values(array: $this->tenantsBySlug);
    }

    public function saveMember(TenantMember $member): void
    {
        $this->members[$this->memberKey(tenantId: $member->tenantId, userId: $member->userId)] = $member;
    }

    private function memberKey(string $tenantId, int $userId): string
    {
        return $tenantId.':'.$userId;
    }

    public function findMember(string $tenantId, int $userId): ?TenantMember
    {
        return $this->members[$this->memberKey(tenantId: $tenantId, userId: $userId)] ?? null;
    }

    public function allMembers(string $tenantId): array
    {
        return array_values(array: array_filter(
            array   : $this->members,
            callback: static fn (TenantMember $member): bool => $member->tenantId === $tenantId,
        ));
    }

    public function removeMember(string $tenantId, int $userId): void
    {
        unset($this->members[$this->memberKey(tenantId: $tenantId, userId: $userId)]);
    }

    public function saveInvite(TenantInvite $invite): void
    {
        $this->invites[$invite->inviteId] = $invite;
    }

    public function findInviteById(string $inviteId): ?TenantInvite
    {
        return $this->invites[$inviteId] ?? null;
    }

    public function findInviteByToken(#[SensitiveParameter] string $plainToken): ?TenantInvite
    {
        $tokenHash = hash(algo: 'sha256', data: $plainToken);

        foreach ($this->invites as $invite) {
            if ($invite->isAccepted()) {
                continue;
            }

            if (hash_equals(known_string: $invite->tokenHash, user_string: $tokenHash)) {
                return $invite;
            }
        }

        return null;
    }

    public function markInviteAccepted(string $inviteId, int $acceptedByUserId, DateTimeImmutable $acceptedAt): void
    {
        $invite = $this->invites[$inviteId] ?? null;

        if ($invite === null) {
            return;
        }

        $this->invites[$inviteId] = new TenantInvite(
            inviteId        : $invite->inviteId,
            tenantId        : $invite->tenantId,
            email           : $invite->email,
            role            : $invite->role,
            tokenHash       : $invite->tokenHash,
            invitedBy       : $invite->invitedBy,
            createdAt       : $invite->createdAt,
            acceptedAt      : $acceptedAt,
            acceptedByUserId: $acceptedByUserId,
        );
    }
}
