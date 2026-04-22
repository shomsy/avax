<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\TerminateSession;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;

final class TerminateSession
{
    private SessionEngine $engine;

    public function __construct(SessionEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle(string $reason = 'logout') : void
    {
        $this->engine->terminate(reason: $reason);
    }
}
