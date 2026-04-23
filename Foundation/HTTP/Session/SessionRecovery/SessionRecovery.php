<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionRecovery;

use Avax\HTTP\Session\SessionStore\SessionStore;
use Exception;

final class SessionRecovery
{
    private SessionStore $store;
    private array        $snapshots = [];

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function backup(string $name = 'default') : void
    {
        $data = $this->store->all();

        $this->snapshots[$name] = [
            'data'      => $data,
            'timestamp' => time(),
            'hash'      => hash('sha256', serialize($data)),
        ];
    }

    public function restore(string $name = 'default') : bool
    {
        if (! isset($this->snapshots[$name])) {
            return false;
        }

        $snapshot = $this->snapshots[$name];

        $currentHash = hash('sha256', serialize($this->store->all()));

        if (! hash_equals($snapshot['hash'], $currentHash)) {
            throw new Exception('Snapshot integrity check failed');
        }

        $this->store->flush();

        foreach ($snapshot['data'] as $key => $value) {
            $this->store->put($key, $value);
        }

        return true;
    }

    public function hasSnapshot(string $name = 'default') : bool
    {
        return isset($this->snapshots[$name]);
    }

    public function deleteSnapshot(string $name = 'default') : bool
    {
        if (! isset($this->snapshots[$name])) {
            return false;
        }

        unset($this->snapshots[$name]);

        return true;
    }
}