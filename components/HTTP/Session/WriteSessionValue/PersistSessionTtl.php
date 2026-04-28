<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\WriteSessionValue;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;

final class PersistSessionTtl
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key, int $ttl) : void
    {
        $this->store->put(key: '_ttl.' . $key, value: time() + $ttl);
    }
}