<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RememberSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class RememberSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key, callable $callback, int|null $ttl = null) : mixed
    {
        if ($this->store->has(key: $key)) {
            return $this->store->get(key: $key);
        }

        $value = $callback();
        $this->store->put(key: $key, value: $value, ttl: $ttl);

        if ($ttl !== null) {
            $this->store->put(key: '_ttl.' . $key, value: time() + $ttl);
        }

        return $value;
    }
}
