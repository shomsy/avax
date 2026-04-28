<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\RegenerateSessionId;

use Random\RandomException;

final class CreateNextSessionId
{
    /**
     * @throws RandomException
     */
    public function handle() : string
    {
        return bin2hex(random_bytes(32));
    }
}