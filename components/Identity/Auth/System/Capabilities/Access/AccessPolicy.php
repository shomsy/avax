<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access;

/**
 * AccessPolicy - Contract for access policies.
 * 1:1 alignment with refactor.md.
 */
interface AccessPolicy
{
    public function allows(object $user, string $permission, mixed $resource = null): bool;
}
