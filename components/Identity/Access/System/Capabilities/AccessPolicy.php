<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities;

/**
 * Legacy callable access-policy contract.
 *
 * This contract represents imperative policy implementations. The canonical
 * declarative policy value object lives in
 * Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy.
 *
 * @deprecated Prefer the canonical Policy\AccessPolicy value object for new
 *             access requirements. Keep this interface only for older custom
 *             policy implementations until a migration path is approved.
 */
interface AccessPolicy
{
    public function allows(object $user, string $permission, mixed $resource = null) : bool;
}
