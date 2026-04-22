<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\ClearSession;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;

final class ClearSession
{
    private SessionEngine $engine;

    public function __construct(SessionEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle() : void
    {
        $this->engine->flush();
    }
}
