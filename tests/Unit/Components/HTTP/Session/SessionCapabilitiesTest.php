<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Session;

use Avax\Components\HTTP\Session\System\System\Capabilities\Storage\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class SessionCapabilitiesTest extends TestCase
{
    public function test_array_session_store_manages_values() : void
    {
        $store = new ArraySessionStore();
        $store->write('session-id', ['key' => 'value']);

        $data = $store->read('session-id');
        $this->assertSame('value', $data['key']);

        $store->destroy('session-id');
        $this->assertEmpty($store->read('session-id'));
    }
}
