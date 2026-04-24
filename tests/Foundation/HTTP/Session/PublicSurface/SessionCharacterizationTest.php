<?php

declare(strict_types=1);

use Avax\HTTP\Session\NullSession;
use PHPUnit\Framework\TestCase;

final class SessionCharacterizationTest extends TestCase
{
    public function test_put_get_has_forget_flush_remember_and_terminate() : void
    {
        $s = new NullSession();

        $this->assertFalse($s->has('foo'));

        $s->put('foo', 'bar');
        $this->assertTrue($s->has('foo'));
        $this->assertSame('bar', $s->get('foo'));

        $val = $s->remember('baz', function () { return 42; });
        $this->assertSame(42, $val);

        $s->forget('foo');
        $this->assertFalse($s->has('foo'));

        $s->put('a', 1);
        $s->flush();
        $this->assertSame([], $s->all());

        $s->put('u', 'user');
        $s->terminate();
        $this->assertSame([], $s->all());
    }

    public function test_getid_and_regenerate_is_noop_for_null_session() : void
    {
        $s = new NullSession();
        $this->assertSame('', $s->getId());
        $s->regenerateId(); // no exception
        $this->assertSame('', $s->getId());
    }
}
