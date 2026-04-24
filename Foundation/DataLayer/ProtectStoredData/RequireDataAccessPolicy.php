<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

use InvalidArgumentException;

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
        return new self('read_only', ['read'], true);
    }

    public static function admin() : self
    {
        return new self('admin', ['read', 'write', 'delete', 'admin'], true);
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