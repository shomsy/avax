<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Arrhae\Arrhae;
use Avax\Components\DataStack\Data\System\Foundation\Failure\MutationException;
use PHPUnit\Framework\TestCase;

final class ArrhaeTest extends TestCase
{
    // -- Factories ----------------------------------------------------------------

    public function test_make_from_array() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2]);

        self::assertSame(['a' => 1, 'b' => 2], $arrhae->all());
    }

    public function test_from_iterable() : void
    {
        $arrhae = Arrhae::from(new ArrayIterator(['x', 'y']));

        self::assertSame(['x', 'y'], $arrhae->all());
    }

    public function test_wrap_scalar() : void
    {
        $arrhae = Arrhae::wrap('hello');

        self::assertSame(['hello'], $arrhae->all());
    }

    public function test_wrap_array() : void
    {
        $arrhae = Arrhae::wrap(['a', 'b']);

        self::assertSame(['a', 'b'], $arrhae->all());
    }

    public function test_wrap_returns_same_instance() : void
    {
        $original = Arrhae::make([1, 2]);
        $wrapped  = Arrhae::wrap($original);

        self::assertSame($original, $wrapped);
    }

    // -- Access -------------------------------------------------------------------

    public function test_all_returns_raw_array() : void
    {
        $arrhae = Arrhae::make(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $arrhae->all());
    }

    public function test_get_existing_key() : void
    {
        $arrhae = Arrhae::make(['name' => 'AvaX']);

        self::assertSame('AvaX', $arrhae->get(key: 'name'));
    }

    public function test_get_missing_key_returns_default() : void
    {
        $arrhae = Arrhae::make([]);

        self::assertNull($arrhae->get(key: 'missing'));
        self::assertSame('fallback', $arrhae->get(key: 'missing', default: 'fallback'));
    }

    public function test_get_dot_notation() : void
    {
        $arrhae = Arrhae::make([
                                   'user' => [
                                       'name'    => 'John',
                                       'address' => [
                                           'city' => 'NYC',
                                       ],
                                   ],
                               ]);

        self::assertSame('John', $arrhae->get(key: 'user.name'));
        self::assertSame('NYC', $arrhae->get(key: 'user.address.city'));
        self::assertNull($arrhae->get(key: 'user.address.zip'));
        self::assertSame('00000', $arrhae->get(key: 'user.address.zip', default: '00000'));
    }

    public function test_has_existing_key() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);

        self::assertTrue($arrhae->has(key: 'a'));
        self::assertFalse($arrhae->has(key: 'b'));
    }

    public function test_has_dot_notation() : void
    {
        $arrhae = Arrhae::make([
                                   'config' => ['debug' => true],
                               ]);

        self::assertTrue($arrhae->has(key: 'config.debug'));
        self::assertFalse($arrhae->has(key: 'config.cache'));
    }

    // -- Mutation -----------------------------------------------------------------

    public function test_set_new_key() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);
        $new    = $arrhae->set(key: 'b', value: 2);

        self::assertSame(['a' => 1, 'b' => 2], $new->all());
        self::assertSame(['a' => 1], $arrhae->all()); // original unchanged
    }

    public function test_set_dot_notation() : void
    {
        $arrhae = Arrhae::make(['user' => ['name' => 'John']]);
        $new    = $arrhae->set(key: 'user.email', value: 'john@example.com');

        self::assertSame('john@example.com', $new->get(key: 'user.email'));
    }

    public function test_forget_key() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2]);
        $new    = $arrhae->forget(key: 'a');

        self::assertSame(['b' => 2], $new->all());
    }

    public function test_forget_dot_notation() : void
    {
        $arrhae = Arrhae::make(['user' => ['name' => 'John', 'age' => 30]]);
        $new    = $arrhae->forget(key: 'user.age');

        self::assertFalse($new->has(key: 'user.age'));
        self::assertTrue($new->has(key: 'user.name'));
    }

    public function test_add_appends() : void
    {
        $arrhae = Arrhae::make([1, 2]);
        $new    = $arrhae->add(value: 3);

        self::assertSame([1, 2, 3], $new->all());
    }

    public function test_merge() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);
        $new    = $arrhae->merge(items: ['b' => 2, 'a' => 3]);

        self::assertSame(['a' => 3, 'b' => 2], $new->all());
    }

    public function test_locked_set_throws() : void
    {
        $arrhae = Arrhae::make(['a' => 1])->lock();

        $this->expectException(MutationException::class);
        (void) $arrhae->set(key: 'b', value: 2);
    }

    public function test_locked_forget_throws() : void
    {
        $arrhae = Arrhae::make(['a' => 1])->lock();

        $this->expectException(MutationException::class);
        (void) $arrhae->forget(key: 'a');
    }

    public function test_locked_add_throws() : void
    {
        $arrhae = Arrhae::make([1])->lock();

        $this->expectException(MutationException::class);
        (void) $arrhae->add(value: 2);
    }

    // -- Selection ----------------------------------------------------------------

    public function test_only() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);

        self::assertSame(['a' => 1, 'b' => 2], $arrhae->only(keys: ['a', 'b'])->all());
    }

    public function test_except() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);

        self::assertSame(['b' => 2, 'c' => 3], $arrhae->except(keys: ['a'])->all());
    }

    public function test_pluck() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'John', 'age' => 30],
                                   ['name' => 'Jane', 'age' => 25],
                               ]);

        self::assertSame(['John', 'Jane'], $arrhae->pluck(key: 'name'));
    }

    // -- Info ---------------------------------------------------------------------

    public function test_count() : void
    {
        $arrhae = Arrhae::make(['a', 'b', 'c']);

        self::assertSame(3, $arrhae->count());
    }

    public function test_is_empty() : void
    {
        self::assertTrue(Arrhae::make([])->isEmpty());
        self::assertFalse(Arrhae::make([1])->isEmpty());
    }

    public function test_is_not_empty() : void
    {
        self::assertFalse(Arrhae::make([])->isNotEmpty());
        self::assertTrue(Arrhae::make([1])->isNotEmpty());
    }

    public function test_first() : void
    {
        $arrhae = Arrhae::make(['a', 'b', 'c']);

        self::assertSame('a', $arrhae->first());
        self::assertSame('default', Arrhae::make([])->first(default: 'default'));
    }

    public function test_last() : void
    {
        $arrhae = Arrhae::make(['a', 'b', 'c']);

        self::assertSame('c', $arrhae->last());
        self::assertSame('default', Arrhae::make([])->last(default: 'default'));
    }

    public function test_keys() : void
    {
        $arrhae = Arrhae::make(['foo' => 'bar', 'baz' => 'qux']);

        self::assertSame(['foo', 'baz'], $arrhae->keys());
    }

    public function test_values_reindex() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2]);
        $new    = $arrhae->values();

        self::assertSame([0 => 1, 1 => 2], $new->all());
    }

    // -- Conversion ---------------------------------------------------------------

    public function test_to_array() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);

        self::assertSame(['a' => 1], $arrhae->toArray());
    }

    public function test_to_json() : void
    {
        $arrhae = Arrhae::make(['name' => 'AvaX']);

        self::assertSame('{"name":"AvaX"}', $arrhae->toJson());
    }

    // -- Immutability -------------------------------------------------------------

    public function test_lock_and_is_locked() : void
    {
        $arrhae = Arrhae::make([1, 2]);

        self::assertFalse($arrhae->isLocked());

        $locked = $arrhae->lock();

        self::assertTrue($locked->isLocked());
    }
}
