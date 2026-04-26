<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RememberSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class PersistRememberedSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->store->put(key: $key, value: $value);

        if ($ttl !== null) {
            $this->store->put(key: '_ttl.' . $key, value: time() + $ttl);
        }
    }
}