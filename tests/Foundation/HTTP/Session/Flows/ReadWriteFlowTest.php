<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\Tests\Session\Flows;

use Avax\HTTP\Session\ReadSessionValue\ReadSessionValue;
use Avax\HTTP\Session\SessionStore\ArraySessionStore;
use Avax\HTTP\Session\WriteSessionValue\WriteSessionValue;
use Avax\Tests\TestCase;

final class ReadWriteFlowTest extends TestCase
{
    public function test_read_value_from_store() : void
    {
        $store = new ArraySessionStore();
        $store->put(key: 'user_id', value: 42);

        $reader = new ReadSessionValue(store: $store);
        $result = $reader->handle(key: 'user_id');

        $this->assertSame(expected: 42, actual: $result);
    }

    public function test_write_value_to_store() : void
    {
        $store = new ArraySessionStore();

        $writer = new WriteSessionValue(store: $store);
        $writer->handle(key: 'user_id', value: 42);

        $this->assertSame(expected: 42, actual: $store->get(key: 'user_id'));
    }

    public function test_write_with_ttl() : void
    {
        $store = new ArraySessionStore();

        $writer = new WriteSessionValue(store: $store);
        $writer->handle(key: 'token', value: 'abc', ttl: 3600);

        $this->assertSame(expected: 'abc', actual: $store->get(key: 'token'));
        $this->assertIsInt(actual: $store->get(key: '_ttl.token'));
    }
}