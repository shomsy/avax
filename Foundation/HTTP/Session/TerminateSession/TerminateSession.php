<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\TerminateSession;

final class TerminateSession
{
    public function handle(string $reason = 'logout') : void
    {
        throw new \RuntimeException('TerminateSession not implemented - placeholder for refactor');
    }
}
