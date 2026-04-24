<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\WriteSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class WriteSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->store->put(key: $key, value: $value, ttl: $ttl);

        if ($ttl !== null) {
            $this->store->put(key: '_ttl.' . $key, value: time() + $ttl);
        }
    }
}
