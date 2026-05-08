<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Exceptions\MutationException;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    // -- Factories ----------------------------------------------------------------

    public function test_make() : void
    {
        $collection = Collection::make([1, 2, 3]);

        self::assertSame([1, 2, 3], $collection->all());
    }

    public function test_wrap() : void
    {
        self::assertSame(['hello'], Collection::wrap('hello')->all());
        self::assertSame([1, 2], Collection::wrap([1, 2])->all());
    }

    // -- Basic Access -------------------------------------------------------------

    public function test_all() : void
    {
        self::assertSame(['a' => 1], Collection::make(['a' => 1])->all());
    }

    public function test_count() : void
    {
        self::assertSame(3, Collection::make([1, 2, 3])->count());
    }

    public function test_is_empty() : void
    {
        self::assertTrue(Collection::make([])->isEmpty());
        self::assertFalse(Collection::make([1])->isEmpty());
    }

    public function test_is_not_empty() : void
    {
        self::assertFalse(Collection::make([])->isNotEmpty());
        self::assertTrue(Collection::make([1])->isNotEmpty());
    }

    public function test_first() : void
    {
        self::assertSame('a', Collection::make(['a', 'b'])->first());
        self::assertSame('default', Collection::make([])->first(default: 'default'));
    }

    public function test_last() : void
    {
        self::assertSame('b', Collection::make(['a', 'b'])->last());
        self::assertSame('default', Collection::make([])->last(default: 'default'));
    }

    // -- Dot-Path Access ----------------------------------------------------------

    public function test_get_simple_key() : void
    {
        $c = Collection::make(['name' => 'AvaX']);

        self::assertSame('AvaX', $c->get(key: 'name'));
        self::assertNull($c->get(key: 'missing'));
        self::assertSame('fallback', $c->get(key: 'missing', default: 'fallback'));
    }

    public function test_get_dot_path() : void
    {
        $c = Collection::make(['user' => ['address' => ['city' => 'NYC']]]);

        self::assertSame('NYC', $c->get(key: 'user.address.city'));
    }

    public function test_has_simple_key() : void
    {
        $c = Collection::make(['a' => 1]);

        self::assertTrue($c->has(key: 'a'));
        self::assertFalse($c->has(key: 'b'));
    }

    public function test_has_dot_path() : void
    {
        $c = Collection::make(['config' => ['debug' => true]]);

        self::assertTrue($c->has(key: 'config.debug'));
    }

    // -- Mutation -----------------------------------------------------------------

    public function test_set() : void
    {
        $c   = Collection::make(['a' => 1]);
        $new = $c->set(key: 'b', value: 2);

        self::assertSame(['a' => 1, 'b' => 2], $new->all());
    }

    public function test_set_dot_path() : void
    {
        $c   = Collection::make(['user' => ['name' => 'John']]);
        $new = $c->set(key: 'user.email', value: 'john@example.com');

        self::assertSame('john@example.com', $new->get(key: 'user.email'));
    }

    public function test_forget() : void
    {
        $c   = Collection::make(['a' => 1, 'b' => 2]);
        $new = $c->forget(key: 'a');

        self::assertSame(['b' => 2], $new->all());
    }

    public function test_add() : void
    {
        $c   = Collection::make([1, 2]);
        $new = $c->add(value: 3);

        self::assertSame([1, 2, 3], $new->all());
    }

    public function test_merge() : void
    {
        $c   = Collection::make(['a' => 1]);
        $new = $c->merge(items: ['b' => 2]);

        self::assertSame(['a' => 1, 'b' => 2], $new->all());
    }

    public function test_pull() : void
    {
        $c    = Collection::make(['a' => 1, 'b' => 2]);
        $pair = $c->pull(key: 'a');

        self::assertSame(1, $pair->first());
        $remaining = $pair->second();
        self::assertInstanceOf(Collection::class, $remaining);
        self::assertSame(['b' => 2], $remaining->all());
    }

    // -- Pipeline -----------------------------------------------------------------

    public function test_map() : void
    {
        $c = Collection::make([1, 2, 3]);

        self::assertSame([2, 4, 6], $c->map(callback: static fn (int $n) : int => $n * 2)->all());
    }

    public function test_filter() : void
    {
        $c = Collection::make([1, 2, 3, 4, 5]);

        self::assertSame([2 => 3, 3 => 4, 4 => 5], $c->filter(callback: static fn (int $n) : bool => $n > 2)->all());
    }

    public function test_reduce() : void
    {
        $c = Collection::make([1, 2, 3]);

        self::assertSame(6, $c->reduce(callback: static fn (int $carry, int $item) : int => $carry + $item, initial: 0));
    }

    public function test_sort() : void
    {
        $c = Collection::make([3, 1, 2]);

        self::assertSame([1, 2, 3], array_values(array: $c->sort()->all()));
    }

    public function test_sort_by() : void
    {
        $c = Collection::make([
                                  ['name' => 'Charlie', 'age' => 35],
                                  ['name' => 'Alice', 'age' => 25],
                              ]);

        $sorted = $c->sortBy(key: 'age');

        self::assertSame('Alice', $sorted->first()['name']);
    }

    public function test_reverse() : void
    {
        $c = Collection::make([1, 2, 3]);

        self::assertSame([2 => 3, 1 => 2, 0 => 1], $c->reverse()->all());
    }

    public function test_shuffle() : void
    {
        $c        = Collection::make([1, 2, 3, 4, 5]);
        $shuffled = $c->shuffle();

        self::assertCount(5, $shuffled->all());
    }

    public function test_unique() : void
    {
        $c = Collection::make([1, 1, 2, 2, 3]);

        self::assertSame([0 => 1, 2 => 2, 4 => 3], $c->unique()->all());
    }

    public function test_chunk() : void
    {
        $c       = Collection::make([1, 2, 3, 4]);
        $chunked = $c->chunk(size: 2);

        // array_chunk with preserve_keys=true keeps original keys
        self::assertSame([[0 => 1, 1 => 2], [2 => 3, 3 => 4]], array_values(array: $chunked->all()));
    }

    public function test_group_by() : void
    {
        $c = Collection::make([
                                  ['type' => 'fruit', 'name' => 'apple'],
                                  ['type' => 'fruit', 'name' => 'banana'],
                                  ['type' => 'vegetable', 'name' => 'carrot'],
                              ]);

        $grouped = $c->groupBy(key: 'type');

        self::assertCount(2, $grouped['fruit']);
        self::assertCount(1, $grouped['vegetable']);
    }

    public function test_partition() : void
    {
        $c = Collection::make([1, 2, 3, 4]);
        [$evens, $odds] = $c->partition(callback: static fn (int $n) : bool => $n % 2 === 0);

        // PartitionValues reindexes via $pass[] = $item
        self::assertInstanceOf(Collection::class, $evens);
        self::assertInstanceOf(Collection::class, $odds);
        self::assertSame([0 => 2, 1 => 4], $evens->all());
        self::assertSame([0 => 1, 1 => 3], $odds->all());
    }

    // -- Filtering ----------------------------------------------------------------

    public function test_where() : void
    {
        $c = Collection::make([
                                  ['name' => 'John', 'age' => 30],
                                  ['name' => 'Jane', 'age' => 25],
                              ]);

        self::assertCount(1, $c->where(key: 'age', value: 30));
    }

    public function test_where_in() : void
    {
        $c = Collection::make([
                                  ['name' => 'John', 'age' => 30],
                                  ['name' => 'Jane', 'age' => 25],
                                  ['name' => 'Bob', 'age' => 30],
                              ]);

        self::assertCount(2, $c->whereIn(key: 'age', values: [30]));
    }

    public function test_where_between() : void
    {
        $c = Collection::make([
                                  ['name' => 'John', 'age' => 30],
                                  ['name' => 'Jane', 'age' => 25],
                                  ['name' => 'Bob', 'age' => 40],
                              ]);

        self::assertCount(2, $c->whereBetween(key: 'age', range: [25, 35]));
    }

    public function test_where_null() : void
    {
        $c = Collection::make([
                                  ['name' => 'John', 'age' => 30],
                                  ['name' => 'Jane', 'age' => null],
                              ]);

        self::assertCount(1, $c->whereNull(key: 'age'));
    }

    public function test_where_not_null() : void
    {
        $c = Collection::make([
                                  ['name' => 'John', 'age' => 30],
                                  ['name' => 'Jane', 'age' => null],
                              ]);

        self::assertCount(1, $c->whereNotNull(key: 'age'));
    }

    // -- Search -------------------------------------------------------------------

    public function test_contains() : void
    {
        $c = Collection::make([1, 2, 3]);

        self::assertTrue($c->contains(value: 2));
        self::assertFalse($c->contains(value: 5));
    }

    public function test_search() : void
    {
        $c = Collection::make(['a', 'b', 'c']);

        self::assertSame(1, $c->search(value: 'b'));
        self::assertFalse($c->search(value: 'z'));
    }

    // -- Aggregation --------------------------------------------------------------

    public function test_sum() : void
    {
        $c = Collection::make([
                                  ['price' => 10],
                                  ['price' => 20],
                              ]);

        self::assertSame(30, $c->sum(key: 'price'));
    }

    public function test_average() : void
    {
        $c = Collection::make([
                                  ['price' => 10],
                                  ['price' => 20],
                              ]);

        self::assertSame(15.0, $c->average(key: 'price'));
    }

    public function test_min() : void
    {
        $c = Collection::make([
                                  ['price' => 10],
                                  ['price' => 20],
                              ]);

        self::assertSame(10, $c->min(key: 'price'));
    }

    public function test_max() : void
    {
        $c = Collection::make([
                                  ['price' => 10],
                                  ['price' => 20],
                              ]);

        self::assertSame(20, $c->max(key: 'price'));
    }

    // -- Selection ----------------------------------------------------------------

    public function test_pluck() : void
    {
        $c = Collection::make([
                                  ['name' => 'John'],
                                  ['name' => 'Jane'],
                              ]);

        self::assertSame(['John', 'Jane'], $c->pluck(key: 'name'));
    }

    public function test_key_by() : void
    {
        $c = Collection::make([
                                  ['id' => 'a', 'name' => 'John'],
                                  ['id' => 'b', 'name' => 'Jane'],
                              ]);

        $keyed = $c->keyBy(key: 'id');

        self::assertSame('John', $keyed->get(key: 'a')['name']);
    }

    public function test_only() : void
    {
        $c = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);

        self::assertSame(['a' => 1, 'b' => 2], $c->only(keys: ['a', 'b'])->all());
    }

    public function test_except() : void
    {
        $c = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);

        self::assertSame(['b' => 2, 'c' => 3], $c->except(keys: ['a'])->all());
    }

    public function test_keys() : void
    {
        $c = Collection::make(['foo' => 'bar', 'baz' => 'qux']);

        self::assertSame(['foo', 'baz'], $c->keys());
    }

    public function test_values() : void
    {
        $c = Collection::make(['a' => 1, 'b' => 2]);

        self::assertSame([0 => 1, 1 => 2], $c->values()->all());
    }

    // -- Set Operations -----------------------------------------------------------

    public function test_flip() : void
    {
        $c = Collection::make(['a', 'b']);

        self::assertSame(['a' => 0, 'b' => 1], $c->flip()->all());
    }

    public function test_union() : void
    {
        $c   = Collection::make(['a' => 1]);
        $new = $c->union(items: ['a' => 2, 'b' => 3]);

        self::assertSame(['a' => 1, 'b' => 3], $new->all());
    }

    public function test_diff() : void
    {
        $c = Collection::make([1, 2, 3]);

        self::assertSame([2 => 3], $c->diff(items: [1, 2])->all());
    }

    public function test_intersect() : void
    {
        $c = Collection::make([1, 2, 3]);

        self::assertSame([0 => 1, 1 => 2], $c->intersect(items: [1, 2, 4])->all());
    }

    // -- Pipeline Control ---------------------------------------------------------

    public function test_tap() : void
    {
        $c      = Collection::make([1, 2, 3]);
        $tapped = null;

        $result = $c->tap(callback: static function (Collection $col) use (&$tapped) : void {
            $tapped = $col->count();
        });

        self::assertSame(3, $tapped);
        self::assertSame($c, $result);
    }

    public function test_when() : void
    {
        $c = Collection::make([1, 2, 3]);

        $result = $c->when(condition: true, callback: static fn (Collection $col) : Collection => $col->filter(callback: static fn (int $n) : bool => $n > 1));

        self::assertSame([1 => 2, 2 => 3], $result->all());
    }

    public function test_unless() : void
    {
        $c = Collection::make([1, 2, 3]);

        $result = $c->unless(condition: false, callback: static fn (Collection $col) : Collection => $col->filter(callback: static fn (int $n) : bool => $n > 1));

        self::assertSame([1 => 2, 2 => 3], $result->all());
    }

    // -- Conversion ---------------------------------------------------------------

    public function test_to_array() : void
    {
        $c = Collection::make(['a' => 1]);

        self::assertSame(['a' => 1], $c->toArray());
    }

    public function test_to_json() : void
    {
        $c = Collection::make(['name' => 'AvaX']);

        self::assertSame('{"name":"AvaX"}', $c->toJson());
    }

    // -- Immutability -------------------------------------------------------------

    public function test_lock() : void
    {
        $c = Collection::make([1, 2]);

        self::assertFalse($c->isLocked());

        $locked = $c->lock();

        self::assertTrue($locked->isLocked());
    }

    public function test_lock_twice_throws() : void
    {
        $c = Collection::make([1, 2])->lock();

        $this->expectException(MutationException::class);
        $c->lock();
    }

    public function test_to_immutable_alias() : void
    {
        $c      = Collection::make([1, 2]);
        $locked = $c->toImmutable();

        self::assertTrue($locked->isLocked());
    }

    // -- offsetSet / offsetUnset Bug Fix ------------------------------------------

    public function test_offset_set_throws() : void
    {
        $c = Collection::make(['a' => 1]);

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Array-style mutation is not supported');
        $c->offsetSet(offset: 'a', value: 999);
    }

    public function test_offset_unset_throws() : void
    {
        $c = Collection::make(['a' => 1]);

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Array-style mutation is not supported');
        $c->offsetUnset(offset: 'a');
    }

    // -- Iterator -----------------------------------------------------------------

    public function test_iterator() : void
    {
        $c      = Collection::make(['a', 'b']);
        $result = [];

        foreach ($c as $key => $value) {
            $result[$key] = $value;
        }

        self::assertSame([0 => 'a', 1 => 'b'], $result);
    }

    public function test_offset_exists() : void
    {
        $c = Collection::make(['a' => 1]);

        self::assertTrue($c->offsetExists(offset: 'a'));
        self::assertFalse($c->offsetExists(offset: 'b'));
    }

    public function test_offset_get() : void
    {
        $c = Collection::make(['a' => 1]);

        self::assertSame(1, $c->get(key: 'a'));
        self::assertNull($c->get(key: 'missing'));
    }
}
