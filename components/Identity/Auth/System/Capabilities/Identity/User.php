<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity;

final readonly class User
{
    public function __construct(
        public string $id,
        public string $email,
        public array  $roles = [],
    ) {}

    public function hasRole(string $role) : bool
    {
        return in_array($role, $this->roles, true);
    }
}
