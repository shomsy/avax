<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantMembership;

/**
 * Represents an invitation to join a tenant.
 */
final readonly class MembershipInvite
{
    public function __construct(
        private string $inviteId,
        private string $tenantId,
        private string $invitedUserId,
        private string $invitedByUserId,
        private string $roleId,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable|null $expiresAt = null,
        private bool $isAccepted = false,
        private \DateTimeImmutable|null $acceptedAt = null
    ) {}

    public function getInviteId() : string
    {
        return $this->inviteId;
    }

    public function getTenantId() : string
    {
        return $this->tenantId;
    }

    public function getInvitedUserId() : string
    {
        return $this->invitedUserId;
    }

    public function getInvitedByUserId() : string
    {
        return $this->invitedByUserId;
    }

    public function getRoleId() : string
    {
        return $this->roleId;
    }

    public function getCreatedAt() : \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt() : ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isAccepted() : bool
    {
        return $this->isAccepted;
    }

    public function getAcceptedAt() : ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function isExpired() : bool
    {
        if ($this->expiresAt === null) {
            return false;
        }
        
        return (new \DateTimeImmutable()) > $this->expiresAt;
    }

    public function accept() : self
    {
        return new self(
            inviteId: $this->inviteId,
            tenantId: $this->tenantId,
            invitedUserId: $this->invitedUserId,
            invitedByUserId: $this->invitedByUserId,
            roleId: $this->roleId,
            createdAt: $this->createdAt,
            expiresAt: $this->expiresAt,
            isAccepted: true,
            acceptedAt: new \DateTimeImmutable()
        );
    }

    public function revoke() : self
    {
        return new self(
            inviteId: $this->inviteId,
            tenantId: $this->tenantId,
            invitedUserId: $this->invitedUserId,
            invitedByUserId: $this->invitedByUserId,
            roleId: $this->roleId,
            createdAt: $this->createdAt,
            expiresAt: $this->expiresAt,
            isAccepted: $this->isAccepted,
            acceptedAt: $this->acceptedAt
        );
    }
}