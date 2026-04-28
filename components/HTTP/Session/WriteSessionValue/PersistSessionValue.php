<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\WriteSessionValue;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;

final class PersistSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key, mixed $value) : void
    {
        $this->store->put(key: $key, value: $value);
    }
}