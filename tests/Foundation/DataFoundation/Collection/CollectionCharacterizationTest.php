<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Collection;

use Avax\Tests\TestCase;
use components\DataFoundation\Collection;
use RuntimeException;

/**
 * Characterization tests for Collection fluent API.
 */
final class CollectionCharacterizationTest extends TestCase
{
    /** READ OPERATIONS */
    public function test_all_returns_items(): void
    {
        $items = ['a' => 1, 'b' => 2];
        $col   = $this->collection(items: $items);

        $this->assertSame($items, $col->all());
    }

    private function collection(array $items = []): Collection
    {
        return new Collection(items: $items);
    }

    public function test_get_returns_value_by_key(): void
    {
        $col = $this->collection(items: ['name' => 'Alice', 'age' => 30]);

        $this->assertSame('Alice', $col->get(key: 'name'));
        $this->assertSame(30, $col->get(key: 'age'));
    }

    public function test_get_returns_default_for_missing_key(): void
    {
        $col = $this->collection(items: ['name' => 'Alice']);

        $this->assertSame('unknown', $col->get(key: 'missing', default: 'unknown'));
    }

    public function test_has_returns_true_for_existing_key(): void
    {
        $col = $this->collection(items: ['name' => 'Alice']);

        $this->assertTrue($col->has(key: 'name'));
        $this->assertFalse($col->has(key: 'missing'));
    }

    public function test_first_returns_first_item(): void
    {
        $col = $this->collection(items: [1, 2, 3]);

        $this->assertSame(1, $col->first());
    }

    public function test_last_returns_last_item(): void
    {
        $col = $this->collection(items: [1, 2, 3]);

        $this->assertSame(3, $col->last());
    }

    /** FLUENT OPERATIONS */
    public function test_map_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3]);
        $result = $col->map(callback: static fn (int $n): int => $n * 2);

        $this->assertSame([2, 4, 6], $result->all());
    }

    public function test_filter_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3, 4]);
        $result = $col->filter(callback: static fn (int $n): bool => $n % 2 === 0);

        $this->assertSame([1 => 2, 3 => 4], $result->all());
    }

    public function test_reduce_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3, 4]);
        $result = $col->reduce(callback: static fn (int $carry, int $n): int => $carry + $n, initial: 0);

        $this->assertSame(10, $result);
    }

    public function test_chain_operations(): void
    {
        $col    = $this->collection(items: [1, 2, 3, 4, 5]);
        $result = $col
            ->filter(callback: static fn (int $n): bool => $n > 2)
            ->map(callback: static fn (int $n): int => $n * 10)
            ->reverse();

        $this->assertSame([50, 40, 30], $result->all());
    }

    /** AGGREGATE OPERATIONS */
    public function test_sum_aggregate(): void
    {
        $col = $this->collection(items: [
                                            ['amount' => 100],
                                            ['amount' => 200],
                                            ['amount' => 150],
                                        ]);

        $this->assertSame(450, $col->sum(key: 'amount'));
    }

    public function test_average_aggregate(): void
    {
        $col = $this->collection(items: [
                                            ['score' => 80],
                                            ['score' => 90],
                                            ['score' => 70],
                                        ]);

        $this->assertSameWithDelta(expected: 80.0, actual: $col->average(key: 'score'), delta: 0.01);
    }

    private function assertSameWithDelta(float $expected, float $actual, float $delta): void
    {
        $this->assertLessThanOrEqual($expected + $delta, $actual);
        $this->assertGreaterThanOrEqual($expected - $delta, $actual);
    }

    public function test_min_max_aggregate(): void
    {
        $col = $this->collection(items: [
                                            ['score' => 80],
                                            ['score' => 90],
                                            ['score' => 70],
                                        ]);

        $this->assertSame(70, $col->min(key: 'score'));
        $this->assertSame(90, $col->max(key: 'score'));
    }

    /** ORDER OPERATIONS */
    public function test_sort_fluent(): void
    {
        $col    = $this->collection(items: [3, 1, 2]);
        $result = $col->sort();

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function test_reverse_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3]);
        $result = $col->reverse();

        $this->assertSame([3, 2, 1], $result->all());
    }

    public function test_shuffle_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3, 4, 5]);
        $result = $col->shuffle();

        $this->assertCount(5, $result->all());
    }

    /** CONVERT OPERATIONS */
    public function test_to_json_fluent(): void
    {
        $col  = $this->collection(items: ['name' => 'Alice', 'age' => 30]);
        $json = $col->toJson();

        $decoded = json_decode(json: $json, associative: true);
        $this->assertSame(['name' => 'Alice', 'age' => 30], $decoded);
    }

    public function test_to_array_fluent(): void
    {
        $col   = $this->collection(items: ['name' => 'Alice']);
        $array = $col->toArray();

        $this->assertSame(['name' => 'Alice'], $array);
    }

    public function test_pluck_fluent(): void
    {
        $col = $this->collection(items: [
                                            ['name' => 'Alice', 'age' => 30],
                                            ['name' => 'Bob', 'age' => 25],
                                        ]);

        $this->assertSame(['Alice', 'Bob'], $col->pluck(key: 'name'));
    }

    public function test_pull_returns_removed_value_and_next_collection(): void
    {
        $col    = $this->collection(items: ['name' => 'Alice', 'age' => 30]);
        $result = $col->pull(key: 'name');

        $this->assertSame('Alice', $result->first());
        $this->assertSame(['age' => 30], $result->second()->all());
        $this->assertSame(['name' => 'Alice', 'age' => 30], $col->all());
    }

    /** LOCK OPERATIONS */
    public function test_lock_fluent(): void
    {
        $col    = $this->collection(items: ['name' => 'Alice']);
        $locked = $col->lock();

        $this->assertTrue($locked->isLocked());
    }

    public function test_immutable_after_lock(): void
    {
        $col    = $this->collection(items: ['name' => 'Alice']);
        $locked = $col->lock();

        $this->expectException(RuntimeException::class);
        $locked->set(key: 'age', value: 30);
    }

    public function test_to_immutable_fluent(): void
    {
        $col       = $this->collection(items: ['name' => 'Alice']);
        $immutable = $col->toImmutable();

        $this->assertTrue($immutable->isLocked());
    }

    /** UTILITY OPERATIONS */
    public function test_chunk_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3, 4, 5]);
        $result = $col->chunk(size: 2);

        $this->assertCount(3, $result->all());
    }

    public function test_group_by_fluent(): void
    {
        $col = $this->collection(items: [
                                            ['category' => 'A', 'name' => 'Alice'],
                                            ['category' => 'B', 'name' => 'Bob'],
                                            ['category' => 'A', 'name' => 'Charlie'],
                                        ]);

        $grouped = $col->groupBy(key: 'category');
        $this->assertCount(2, $grouped['A']);
        $this->assertCount(1, $grouped['B']);
    }

    public function test_unique_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 2, 3, 3, 3]);
        $result = $col->unique();

        $this->assertCount(3, $result->all());
    }

    public function test_partition_fluent(): void
    {
        $col            = $this->collection(items: [1, 2, 3, 4]);
        [$evens, $odds] = $col->partition(callback: static fn (int $n): bool => $n % 2 === 0);

        $this->assertSame([1 => 2, 3 => 4], $evens->all());
        $this->assertSame([0 => 1, 2 => 3], $odds->all());
    }

    public function test_tap_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3]);
        $tapped = null;

        $col->tap(callback: static fn ($c) => $tapped = $c->count());

        $this->assertSame(3, $tapped);
    }

    public function test_when_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3]);
        $result = $col->when(condition: true, callback: static fn ($c) => $c->map(callback: static fn ($n) => $n * 2));

        $this->assertSame([2, 4, 6], $result->all());
    }

    public function test_unless_fluent(): void
    {
        $col    = $this->collection(items: [1, 2, 3]);
        $result = $col->unless(condition: false, callback: static fn ($c) => $c->map(static fn ($n) => $n * 2));

        $this->assertSame([2, 4, 6], $result->all());
    }

    /** STATIC FACTORIES */
    public function test_make_static(): void
    {
        $col = Collection::make(items: ['name' => 'Alice']);

        $this->assertSame(['name' => 'Alice'], $col->all());
    }

    public function test_wrap_static(): void
    {
        $col = Collection::wrap(value: 'single');

        $this->assertSame(['single'], $col->all());
    }

    public function test_wrap_preserves_collection(): void
    {
        $original = $this->collection(items: ['name' => 'Alice']);
        $wrapped  = Collection::wrap(value: $original);

        $this->assertSame($original, $wrapped);
    }

    /** INTERFACE */
    public function test_implements_iterator_aggregate(): void
    {
        $col   = $this->collection(items: [1, 2, 3]);
        $items = iterator_to_array(iterator: $col);

        $this->assertSame([1, 2, 3], $items);
    }

    public function test_implements_countable(): void
    {
        $col = $this->collection(items: [1, 2, 3]);

        $this->assertSame(3, $col->count());
    }

    public function test_implements_array_access(): void
    {
        $col = $this->collection(items: ['name' => 'Alice']);

        $this->assertTrue(isset($col['name']));
        $this->assertSame('Alice', $col['name']);
    }
}
