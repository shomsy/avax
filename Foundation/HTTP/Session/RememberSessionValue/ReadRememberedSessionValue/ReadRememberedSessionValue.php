<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RememberSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class ReadRememberedSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key) : mixed
    {
        return $this->store->get($key);
    }
}