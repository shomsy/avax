<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RegenerateSessionId;

final class CreateNextSessionId
{
    public function handle() : string
    {
        return bin2hex(random_bytes(32));
    }
}