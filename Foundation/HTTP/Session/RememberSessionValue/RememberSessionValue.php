<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RememberSessionValue;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;

final class RememberSessionValue
{
    private SessionEngine $engine;

    public function __construct(SessionEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle(string $key, callable $callback, int|null $ttl = null) : mixed
    {
        $store = $this->engine->storage();

        if ($store->has(key: $key)) {
            return $this->engine->get(key: $key);
        }

        $value = $callback();
        $this->engine->put(key: $key, value: $value, ttl: $ttl);

        return $value;
    }
}
