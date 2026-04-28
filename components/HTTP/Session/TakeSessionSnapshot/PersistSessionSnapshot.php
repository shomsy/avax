<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\TakeSessionSnapshot;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;

final class PersistSessionSnapshot
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $name, array $snapshot) : void
    {
        $key = '_snapshot.' . $name;
        $this->store->put(key: $key, value: $snapshot);
    }
}