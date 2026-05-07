<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle;

use RuntimeException;

final class LifecycleFailed extends RuntimeException
{
    public static function userNotFound(int $userId) : self
    {
        return new self(message: sprintf('Lifecycle target user [%d] was not found.', $userId));
    }

    public static function transitionNotAllowed(LifecycleState $from, LifecycleState $to) : self
    {
        return new self(message: sprintf('Lifecycle transition from [%s] to [%s] is not allowed.', $from->value, $to->value));
    }
}
