<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity;

final class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly array  $roles = [],
    ) {}

    public function hasRole(string $role) : bool
    {
        return in_array($role, $this->roles, true);
    }
}