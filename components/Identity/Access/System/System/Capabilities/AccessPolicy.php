<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities;

/**
 * AccessPolicy - Contract for access policies.
 * 1:1 alignment with refactor.md.
 */
interface AccessPolicy
{
    public function allows(object $user, string $permission, mixed $resource = null) : bool;
}
