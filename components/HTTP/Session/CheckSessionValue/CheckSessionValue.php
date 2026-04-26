<?php

declare(strict_types=1);

namespace components\HTTP\Session\CheckSessionValue;

use components\HTTP\Session\SessionStore\SessionStore;

final class CheckSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store) { $this->store = $store; }

    public function handle(string $key) : bool { return $this->store->has(key: $key); }
}
