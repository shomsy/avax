<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Session;

use RuntimeException;

final class SessionRegistryUnavailable extends RuntimeException
{
    public static function forSessionManagementFlow() : self
    {
        return new self(message: 'Tracked session management requires a configured session registry.');
    }
}
