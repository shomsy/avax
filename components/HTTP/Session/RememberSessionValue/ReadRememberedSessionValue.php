<?php

declare(strict_types=1);

namespace components\HTTP\Session\RememberSessionValue;

use components\HTTP\Session\SessionStore\SessionStore;

final class ReadRememberedSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key) : mixed
    {
        return $this->store->get(key: $key);
    }
}