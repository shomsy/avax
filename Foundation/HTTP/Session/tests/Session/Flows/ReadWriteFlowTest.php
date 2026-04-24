<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Tests\Session\Flows;

use PHPUnit\Framework\TestCase;
use Avax\HTTP\Session\ReadSessionValue\ReadSessionValue;
use Avax\HTTP\Session\WriteSessionValue\WriteSessionValue;
use Avax\HTTP\Session\SessionStore\ArraySessionStore;

final class ReadWriteFlowTest extends TestCase
{
    public function test_read_value_from_store(): void
    {
        $store = new ArraySessionStore();
        $store->put('user_id', 42);
        
        $reader = new ReadSessionValue($store);
        $result = $reader->handle('user_id');
        
        $this->assertSame(42, $result);
    }

    public function test_write_value_to_store(): void
    {
        $store = new ArraySessionStore();
        
        $writer = new WriteSessionValue($store);
        $writer->handle('user_id', 42);
        
        $this->assertSame(42, $store->get('user_id'));
    }

    public function test_write_with_ttl(): void
    {
        $store = new ArraySessionStore();
        
        $writer = new WriteSessionValue($store);
        $writer->handle('token', 'abc', 3600);
        
        $this->assertSame('abc', $store->get('token'));
        $this->assertIsInt($store->get('_ttl.token'));
    }
}