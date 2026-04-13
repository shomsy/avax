<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantMembership;

/**
 * Represents a tenant in the system.
 */
final readonly class Tenant
{
    public function __construct(
        private string $tenantId,
        private string $name,
        private string $slug,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable|null $updatedAt = null,
        private bool $isActive = true
    ) {}

    public function getTenantId() : string
    {
        return $this->tenantId;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getSlug() : string
    {
        return $this->slug;
    }

    public function getCreatedAt() : \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt() : ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isActive() : bool
    {
        return $this->isActive;
    }

    public function deactivate() : self
    {
        return new self(
            tenantId: $this->tenantId,
            name: $this->name,
            slug: $this->slug,
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
            isActive: false
        );
    }

    public function activate() : self
    {
        return new self(
            tenantId: $this->tenantId,
            name: $this->name,
            slug: $this->slug,
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
            isActive: true
        );
    }
}