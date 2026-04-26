<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\Capabilities;

use Avax\HTTP\Session\Recovery\Recovery;
use Avax\HTTP\Session\Shared\Contracts\Storage\StoreInterface;
use Avax\Tests\TestCase;

final class RecoveryCharacterizationTest extends TestCase
{
    public function test_backup_restore_and_transaction_commit_and_rollback() : void
    {
        // Fake in-memory store implementing StoreInterface
        $store = new class implements StoreInterface {
            private array $data = [];

            public function get(string $key, mixed $default = null) : mixed { return $this->data[$key] ?? $default; }

            public function put(string $key, mixed $value, int|null $ttl = null) : void { $this->data[$key] = $value; }

            public function has(string $key) : bool { return array_key_exists($key, $this->data); }

            public function delete(string $key) : void { unset($this->data[$key]); }

            public function all() : array { return $this->data; }

            public function flush() : void { $this->data = []; }

            public function flushNamespace(string $prefix) : void
            {
                foreach (array_keys($this->data) as $k) {
                    if (str_starts_with($k, $prefix)) unset($this->data[$k]);
                }
            }
        };

        $recovery = new Recovery(store: $store, audit: null);

        $store->put(key: 'a', value: 1);
        $recovery->backup(name: 'one');
        $this->assertTrue($recovery->hasBackup(name: 'one'));

        // modify store, then restore
        $store->put(key: 'a', value: 2);
        $this->assertSame(2, $store->get(key: 'a'));

        $recovery->restore(name: 'one');
        $this->assertSame(1, $store->get(key: 'a'));

        // transaction commit
        $store->put(key: 'x', value: 'orig');
        $recovery->beginTransaction();
        $store->put(key: 'x', value: 'changed');
        $recovery->commit();
        $this->assertSame('changed', $store->get(key: 'x'));

        // transaction rollback
        $store->put(key: 'y', value: 'origY');
        try {
            $recovery->transaction(operation: static function () use ($store) {
                $store->put(key: 'y', value: 'inTx');
                throw new RuntimeException(message: 'boom');
            });
            $this->fail('Expected RecoveryException');
        } catch (Throwable $e) {
            // after rollback, original value restored
            $this->assertSame('origY', $store->get(key: 'y'));
        }
    }

    public function test_export_and_import_and_invalid_import() : void
    {
        $store = new class implements StoreInterface {
            private array $data = [];

            public function get(string $key, mixed $default = null) : mixed { return $this->data[$key] ?? $default; }

            public function put(string $key, mixed $value, int|null $ttl = null) : void { $this->data[$key] = $value; }

            public function has(string $key) : bool { return array_key_exists($key, $this->data); }

            public function delete(string $key) : void { unset($this->data[$key]); }

            public function all() : array { return $this->data; }

            public function flush() : void { $this->data = []; }

            public function flushNamespace(string $prefix) : void
            {
                foreach (array_keys($this->data) as $k) {
                    if (str_starts_with($k, $prefix)) unset($this->data[$k]);
                }
            }
        };

        $recovery = new Recovery(store: $store, audit: null);

        $store->put(key: 'p', value: 123);
        $exported = $recovery->export();
        $this->assertIsString($exported);

        $store->flush();
        $this->assertEmpty($store->all());

        $ok = $recovery->import(data: $exported);
        $this->assertTrue($ok);
        $this->assertSame(123, $store->get(key: 'p'));

        // invalid import
        $this->assertFalse($recovery->import(data: 'not a serialized array'));
    }
}
