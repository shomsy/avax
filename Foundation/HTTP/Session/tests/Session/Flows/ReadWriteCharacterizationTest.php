<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Avax\HTTP\Session\NullSession;

final class ReadWriteCharacterizationTest extends TestCase
{
    public function test_read_write_has_behaviour(): void
    {
        $s = new NullSession();

        $this->assertFalse($s->has('k'));
        $this->assertNull($s->get('k'));

        $s->put('k', ['x' => 1]);
        $this->assertTrue($s->has('k'));
        $this->assertSame(['x' => 1], $s->get('k'));

        $s->delete('k');
        $this->assertFalse($s->has('k'));
    }
}
