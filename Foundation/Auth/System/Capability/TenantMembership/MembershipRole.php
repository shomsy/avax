<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantMembership;

/**
 * Represents a role within a tenant that can be assigned to members.
 */
final readonly class MembershipRole
{
    public function __construct(
        private string $roleId,
        private string $tenantId,
        private string $name,
        private string $description,
        private array $permissions = []
    ) {}

    public function getRoleId() : string
    {
        return $this->roleId;
    }

    public function getTenantId() : string
    {
        return $this->tenantId;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @return array<string>
     */
    public function getPermissions() : array
    {
        return $this->permissions;
    }
}