<?php

declare(strict_types=1);

use Avax\HTTP\Session\Recovery\Recovery;
use Avax\HTTP\Session\Shared\Contracts\Storage\StoreInterface;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class SessionImportSecurityTest extends TestCase
{
    public function test_import_rejects_non_array_payloads_and_handles_malformed() : void
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

        // safe unserialize should reject data that unserializes to an object
        $malicious = 'O:8:"stdClass":0:{}';
        $this->assertFalse($recovery->import($malicious));

        // malformed string
        $this->assertFalse($recovery->import('not serialized'));
    }
}
