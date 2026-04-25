<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

final readonly class RequireDataAccessPolicy
{
    public function __construct(
        public string $policyName,
        public array  $requiredPermissions,
        public bool   $enforce
    ) {}

    public function describeResponsibility() : string
    {
        return 'requires data access policy to be enforced before operations.';
    }

    public static function readOnly() : self
    {
        return new self(policyName: 'read_only', requiredPermissions: ['read'], enforce: true);
    }

    public static function admin() : self
    {
        return new self(policyName: 'admin', requiredPermissions: ['read', 'write', 'delete', 'admin'], enforce: true);
    }

    public function checkPermissions(array $userPermissions) : bool
    {
        if (! $this->enforce) {
            return true;
        }

        return count(array_diff($this->requiredPermissions, $userPermissions)) === 0;
    }

    public function toMetadata() : array
    {
        return [
            'policy_name'          => $this->policyName,
            'required_permissions' => $this->requiredPermissions,
            'enforce'              => $this->enforce,
        ];
    }
}