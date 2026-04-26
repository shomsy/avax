<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionRecovery;

use Avax\HTTP\Session\Foundation\SafeSerializer;
use Avax\HTTP\Session\SessionStore\SessionStore;
use Exception;
use RuntimeException;
use Throwable;

final class SessionRecovery
{
    private SessionStore $store;
    private array        $snapshots = [];

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function snapshot(string $name = 'default') : void
    {
        $this->backup(name: $name);
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

    public function hasSnapshot(string $name = 'default') : bool
    {
        return isset($this->snapshots[$name]);
    }

    /**
     * @throws Exception
     */
    public function transaction(callable $operation, string $backupName = '') : mixed
    {
        $backupName = $backupName !== '' ? $backupName : 'transaction_' . uniqid();
        $this->backup(name: $backupName);

        try {
            $result = $operation();
            $this->deleteSnapshot(name: $backupName);

            return $result;
        } catch (Throwable $throwable) {
            $this->restore(name: $backupName);
            $this->deleteSnapshot(name: $backupName);

            throw new RuntimeException(
                message : 'Session transaction failed: ' . $throwable->getMessage(),
                previous: $throwable
            );
        }
    }

    public function deleteSnapshot(string $name = 'default') : bool
    {
        if (! isset($this->snapshots[$name])) {
            return false;
        }

        unset($this->snapshots[$name]);

        return true;
    }

    /**
     * @throws Exception
     */
    public function restore(string $name = 'default') : bool
    {
        if (! isset($this->snapshots[$name])) {
            return false;
        }

        $snapshot = $this->snapshots[$name];

        $currentHash = hash('sha256', serialize($this->store->all()));

        if (! hash_equals($snapshot['hash'], $currentHash)) {
            throw new Exception(message: 'Snapshot integrity check failed');
        }

        $this->store->flush();

        foreach ($snapshot['data'] as $key => $value) {
            $this->store->put(key: $key, value: $value);
        }

        return true;
    }

    public function export() : string
    {
        return serialize(value: $this->store->all());
    }

    public function import(string $payload) : bool
    {
        try {
            $serializer = new SafeSerializer();
            $data       = $serializer->unserialize(data: $payload);

            if (! is_array(value: $data)) {
                return false;
            }

            $this->store->flush();

            foreach ($data as $key => $value) {
                $this->store->put(key: $key, value: $value);
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
