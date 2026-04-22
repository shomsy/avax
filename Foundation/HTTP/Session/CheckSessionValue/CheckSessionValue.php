<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\CheckSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class CheckSessionValue
{
    private SessionStore $store;
    public function __construct(SessionStore $store) { $this->store = $store; }
    public function handle(string $key) : bool { return $this->store->has(key: $key); }
}
