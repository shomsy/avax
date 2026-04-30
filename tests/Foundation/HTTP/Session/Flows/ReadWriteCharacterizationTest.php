<?php

declare(strict_types=1);


namespace Avax\Tests\Foundation\HTTP\Session\Flows;

use Avax\Components\HTTP\Session\NullSession;
use Avax\Tests\TestCase;

final class ReadWriteCharacterizationTest extends TestCase
{
    public function test_read_write_has_behaviour() : void
    {
        $s = new NullSession();

        $this->assertFalse($s->has(key: 'k'));
        $this->assertNull($s->get(key: 'k'));

        $s->put(key: 'k', value: ['x' => 1]);
        $this->assertTrue($s->has(key: 'k'));
        $this->assertSame(['x' => 1], $s->get(key: 'k'));

        $s->delete(key: 'k');
        $this->assertFalse($s->has(key: 'k'));
    }
}
