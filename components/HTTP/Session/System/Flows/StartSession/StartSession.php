<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\StartSession;

final class StartSession
{
    public function execute(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            return session_start();
        }

        return true;
    }
}
