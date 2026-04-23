<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\ReadSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class ReadStoredSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key, mixed $default = null) : mixed
    {
        return $this->store->get($key, $default);
    }
}