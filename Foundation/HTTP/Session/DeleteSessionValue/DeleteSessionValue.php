<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\DeleteSessionValue;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;

final class DeleteSessionValue
{
    private SessionEngine $engine;

    public function __construct(SessionEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle(string $key) : void
    {
        $this->engine->storage()->delete(key: $key);
    }
}
