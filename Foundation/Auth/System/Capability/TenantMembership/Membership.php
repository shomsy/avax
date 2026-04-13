<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantMembership;

/**
 * Represents a membership relationship between a user and a tenant.
 */
final readonly class Membership
{
    public function __construct(
        private string $membershipId,
        private string $userId,
        private string $tenantId,
        private string $roleId,
        private \DateTimeImmutable $joinedAt,
        private \DateTimeImmutable|null $leftAt = null,
        private bool $isActive = true
    ) {}

    public function getMembershipId() : string
    {
        return $this->membershipId;
    }

    public function getUserId() : string
    {
        return $this->userId;
    }

    public function getTenantId() : string
    {
        return $this->tenantId;
    }

    public function getRoleId() : string
    {
        return $this->roleId;
    }

    public function getJoinedAt() : \DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function getLeftAt() : ?\DateTimeImmutable
    {
        return $this->leftAt;
    }

    public function isActive() : bool
    {
        return $this->isActive;
    }

    public function leave() : self
    {
        return new self(
            membershipId: $this->membershipId,
            userId: $this->userId,
            tenantId: $this->tenantId,
            roleId: $this->roleId,
            joinedAt: $this->joinedAt,
            leftAt: new \DateTimeImmutable(),
            isActive: false
        );
    }
}