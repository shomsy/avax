<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\WriteSessionValue;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;

final class WriteSessionValue
{
    private SessionEngine $engine;

    public function __construct(SessionEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->engine->put(key: $key, value: $value, ttl: $ttl);
    }
}
