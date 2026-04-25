<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\DeleteSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class DropStoredSessionValue
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $key) : void
    {
        $this->store->delete(key: $key);
    }
}