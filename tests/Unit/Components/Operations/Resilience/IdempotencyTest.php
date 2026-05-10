<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\Idempotency;
use Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\Keys\IdempotencyStore;
use Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\Keys\InMemoryIdempotencyStore;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class IdempotencyTest extends TestCase
{
    #[Test]
    public function it_checks_key_not_present() : void
    {
        self::assertFalse(Idempotency::check('nonexistent-key'));
    }

    #[Test]
    public function it_checks_key_after_recording() : void
    {
        Idempotency::record('test-key', ['status' => 'success']);

        self::assertTrue(Idempotency::check('test-key'));
    }

    #[Test]
    public function it_records_response_data() : void
    {
        $responseData = [
            'status'  => 200,
            'body'    => ['message' => 'ok'],
            'headers' => ['X-Request-Id' => 'req-123'],
        ];

        Idempotency::record('record-test', $responseData);

        $replayed = Idempotency::replay('record-test');

        self::assertSame($responseData, $replayed);
    }

    #[Test]
    public function it_replays_recorded_response() : void
    {
        Idempotency::record('replay-test', ['result' => 'cached']);

        $replayed = Idempotency::replay('replay-test');

        self::assertSame(['result' => 'cached'], $replayed);
    }

    #[Test]
    public function it_returns_null_for_unrecorded_key() : void
    {
        self::assertNull(Idempotency::replay('missing-key'));
    }

    #[Test]
    public function it_generates_unique_idempotency_key() : void
    {
        $key1 = Idempotency::generate();
        $key2 = Idempotency::generate();

        self::assertNotSame($key1, $key2);
    }

    #[Test]
    public function it_generates_32_character_hex_key() : void
    {
        $key = Idempotency::generate();

        self::assertSame(32, strlen($key));
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $key);
    }

    #[Test]
    public function it_parses_header_to_key() : void
    {
        $key = Idempotency::fromHeader('abc123');

        self::assertSame('abc123', $key);
    }

    #[Test]
    public function it_returns_null_for_empty_header() : void
    {
        $key = Idempotency::fromHeader('');

        self::assertNull($key);
    }

    #[Test]
    public function it_records_multiple_independent_keys() : void
    {
        Idempotency::record('key-a', ['data' => 'A']);
        Idempotency::record('key-b', ['data' => 'B']);

        self::assertSame(['data' => 'A'], Idempotency::replay('key-a'));
        self::assertSame(['data' => 'B'], Idempotency::replay('key-b'));
    }

    #[Test]
    public function it_overwrites_existing_key() : void
    {
        Idempotency::record('overwrite-test', ['version' => 1]);
        Idempotency::record('overwrite-test', ['version' => 2]);

        $replayed = Idempotency::replay('overwrite-test');

        self::assertSame(['version' => 2], $replayed);
    }

    #[Test]
    public function it_records_empty_response() : void
    {
        Idempotency::record('empty-response', []);

        self::assertSame([], Idempotency::replay('empty-response'));
    }

    #[Test]
    public function it_records_complex_response() : void
    {
        $complexResponse = [
            'user'   => ['id' => 1, 'name' => 'Alice'],
            'orders' => [
                ['id' => 101, 'total' => 99.99],
                ['id' => 102, 'total' => 149.99],
            ],
            'meta'   => ['page' => 1, 'perPage' => 10],
        ];

        Idempotency::record('complex-response', $complexResponse);

        $replayed = Idempotency::replay('complex-response');

        self::assertSame($complexResponse, $replayed);
    }

    #[Test]
    public function it_uses_default_ttl_of_86400() : void
    {
        putenv('IDEMPOTENCY_TTL');

        $reflection = new ReflectionClass(Idempotency::class);
        $method     = $reflection->getMethod('getTtl');

        $ttl = $method->invoke(null);

        self::assertSame(86400, $ttl);
    }

    #[Test]
    public function it_uses_custom_ttl_from_environment() : void
    {
        putenv('IDEMPOTENCY_TTL=3600');

        $reflection = new ReflectionClass(Idempotency::class);
        $method     = $reflection->getMethod('getTtl');

        $ttl = $method->invoke(null);

        self::assertSame(3600, $ttl);
    }

    #[Test]
    public function in_memory_store_implements_idempotency_store_interface() : void
    {
        $store = new InMemoryIdempotencyStore();

        self::assertInstanceOf(IdempotencyStore::class, $store);
    }

    #[Test]
    public function in_memory_store_returns_null_for_missing_key() : void
    {
        $store = new InMemoryIdempotencyStore();

        self::assertNull($store->get('missing'));
    }

    #[Test]
    public function in_memory_store_stores_and_retrieves_value() : void
    {
        $store = new InMemoryIdempotencyStore();

        $store->set('my-key', ['value' => 'data'], ttl: 60);

        self::assertSame(['value' => 'data'], $store->get('my-key'));
    }

    #[Test]
    public function in_memory_store_reports_key_exists() : void
    {
        $store = new InMemoryIdempotencyStore();

        self::assertFalse($store->has('test-key'));

        $store->set('test-key', ['exists' => true], ttl: 60);

        self::assertTrue($store->has('test-key'));
    }

    #[Test]
    public function in_memory_store_does_not_expire_valid_entries() : void
    {
        $store = new InMemoryIdempotencyStore();

        $store->set('valid-key', ['data' => 'fresh'], ttl: 3600);

        self::assertSame(['data' => 'fresh'], $store->get('valid-key'));
    }

    #[Test]
    public function in_memory_store_handles_empty_value() : void
    {
        $store = new InMemoryIdempotencyStore();

        $store->set('empty-value', [], ttl: 60);

        self::assertSame([], $store->get('empty-value'));
        self::assertTrue($store->has('empty-value'));
    }

    #[Test]
    public function in_memory_store_overwrites_existing_key() : void
    {
        $store = new InMemoryIdempotencyStore();

        $store->set('update-key', ['version' => 1], ttl: 60);
        $store->set('update-key', ['version' => 2], ttl: 60);

        self::assertSame(['version' => 2], $store->get('update-key'));
    }

    #[Test]
    public function idempotency_check_uses_store_has_method() : void
    {
        Idempotency::record('check-store-test', ['data' => 'test']);

        self::assertTrue(Idempotency::check('check-store-test'));
        self::assertFalse(Idempotency::check('nonexistent-store-key'));
    }

    #[Test]
    public function idempotency_replay_returns_recorded_data() : void
    {
        Idempotency::record('replay-store-test', ['id' => 123, 'name' => 'test']);

        $replayed = Idempotency::replay('replay-store-test');

        self::assertSame(['id' => 123, 'name' => 'test'], $replayed);
    }

    protected function tearDown() : void
    {
        $reflection = new ReflectionClass(Idempotency::class);
        $property   = $reflection->getProperty('idempotencyStore');
        $property->setValue(null, new InMemoryIdempotencyStore());

        putenv('IDEMPOTENCY_TTL');
    }

}
