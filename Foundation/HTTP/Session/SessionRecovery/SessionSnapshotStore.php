<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionRecovery;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class SessionSnapshotStore
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function put(string $name, array $snapshot) : void
    {
        $this->store->put('_snapshot.' . $name, $snapshot);
    }

    public function get(string $name) : ?array
    {
        return $this->store->get('_snapshot.' . $name);
    }

    public function has(string $name) : bool
    {
        return $this->store->has('_snapshot.' . $name);
    }

    public function delete(string $name) : void
    {
        $this->store->delete('_snapshot.' . $name);
    }
}