<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\Lifecycle;

use RuntimeException;

final class LifecycleFailed extends RuntimeException
{
    public static function userNotFound(int $userId) : self
    {
        return new self(message: "Lifecycle target user [{$userId}] was not found.");
    }

    public static function transitionNotAllowed(LifecycleState $from, LifecycleState $to) : self
    {
        return new self(message: "Lifecycle transition from [{$from->value}] to [{$to->value}] is not allowed.");
    }
}
