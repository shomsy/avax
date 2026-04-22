<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RegenerateSessionId;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;

final class RegenerateSessionId
{
    private SessionEngine $engine;

    public function __construct(SessionEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle() : void
    {
        $this->engine->regenerate();
    }
}
