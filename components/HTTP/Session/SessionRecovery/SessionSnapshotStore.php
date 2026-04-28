<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionRecovery;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;

final class SessionSnapshotStore
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function put(string $name, array $snapshot) : void
    {
        $this->store->put(key: '_snapshot.' . $name, value: $snapshot);
    }

    public function get(string $name) : array|null
    {
        return $this->store->get(key: '_snapshot.' . $name);
    }

    public function has(string $name) : bool
    {
        return $this->store->has(key: '_snapshot.' . $name);
    }

    public function delete(string $name) : void
    {
        $this->store->delete(key: '_snapshot.' . $name);
    }
}