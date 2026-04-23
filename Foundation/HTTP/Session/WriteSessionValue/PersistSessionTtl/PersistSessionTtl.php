<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\WriteSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class PersistSessionTtl
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key, int $ttl) : void
    {
        $this->store->put('_ttl.' . $key, time() + $ttl);
    }
}