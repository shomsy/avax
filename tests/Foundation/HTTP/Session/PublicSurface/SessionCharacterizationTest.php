<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\PublicSurface;

use Avax\Components\HTTP\Session\NullSession;
use Avax\Tests\TestCase;

final class SessionCharacterizationTest extends TestCase
{
    public function test_put_get_has_forget_flush_remember_and_terminate() : void
    {
        $s = new NullSession();

        $this->assertFalse($s->has(key: 'foo'));

        $s->put(key: 'foo', value: 'bar');
        $this->assertTrue($s->has(key: 'foo'));
        $this->assertSame('bar', $s->get(key: 'foo'));

        $val = $s->remember(key: 'baz', callback: static fn () => 42);
        $this->assertSame(42, $val);

        $s->forget(key: 'foo');
        $this->assertFalse($s->has(key: 'foo'));

        $s->put(key: 'a', value: 1);
        $s->flush();
        $this->assertSame([], $s->all());

        $s->put(key: 'u', value: 'user');
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
