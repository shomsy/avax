<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Arrhae;

use ArrayIterator;
use Avax\Tests\TestCase;
use components\DataFoundation\Arrhae;
use RuntimeException;

/**
 * Characterization tests for Arrhae public API.
 *
 * These tests protect existing behavior during refactor.
 */
final class ArrhaeCharacterizationTest extends TestCase
{
    /** READ OPERATIONS */
    public function test_all_returns_items(): void
    {
        $items = ['a' => 1, 'b' => 2];
        $arrh  = $this->arrhae(items: $items);

        $this->assertSame($items, $arrh->all());
    }

    private function arrhae(array $items = []): Arrhae
    {
        return new Arrhae(items: $items);
    }

    public function test_get_returns_value_by_key(): void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice', 'age' => 30]);

        $this->assertSame('Alice', $arrh->get(key: 'name'));
        $this->assertSame(30, $arrh->get(key: 'age'));
    }

    public function test_get_returns_default_for_missing_key(): void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice']);

        $this->assertSame('unknown', $arrh->get(key: 'missing', default: 'unknown'));
        $this->assertNull($arrh->get(key: 'missing'));
    }

    public function test_get_supports_dot_notation(): void
    {
        $arrh = $this->arrhae(items: [
                                         'user' => [
                                             'name'    => 'Alice',
                                             'address' => ['city' => 'Wonderland'],
                                         ],
                                     ]);

        $this->assertSame('Alice', $arrh->get(key: 'user.name'));
        $this->assertSame('Wonderland', $arrh->get(key: 'user.address.city'));
        $this->assertNull($arrh->get(key: 'user.missing'));
    }

    public function test_has_returns_true_for_existing_key(): void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice']);

        $this->assertTrue($arrh->has(key: 'name'));
        $this->assertFalse($arrh->has(key: 'missing'));
    }

    public function test_has_supports_dot_notation(): void
    {
        $arrh = $this->arrhae(items: [
                                         'user' => ['name' => 'Alice'],
                                     ]);

        $this->assertTrue($arrh->has(key: 'user.name'));
        $this->assertFalse($arrh->has(key: 'user.missing'));
        $this->assertFalse($arrh->has(key: 'user'));
    }

    public function test_first_returns_first_item(): void
    {
        $arrh = $this->arrhae(items: [1, 2, 3]);

        $this->assertSame(1, $arrh->first());
    }

    public function test_first_returns_default_for_empty(): void
    {
        $arrh = $this->arrhae(items: []);

        $this->assertNull($arrh->first());
        $this->assertSame('default', $arrh->first(default: 'default'));
    }

    public function test_last_returns_last_item(): void
    {
        $arrh = $this->arrhae(items: [1, 2, 3]);

        $this->assertSame(3, $arrh->last());
    }

    public function test_pluck_extracts_values_by_key(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['name' => 'Alice', 'age' => 30],
                                         ['name' => 'Bob', 'age' => 25],
                                     ]);

        $this->assertSame(['Alice', 'Bob'], $arrh->pluck(key: 'name'));
        $this->assertSame([30, 25], $arrh->pluck(key: 'age'));
    }

    public function test_pluck_with_callable(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['amount' => 100],
                                         ['amount' => 200],
                                     ]);

        $plucked = $arrh->pluck(key: static fn (array $item): int => $item['amount'] * 2);
        $this->assertSame([200, 400], $plucked);
    }

    /** WRITE OPERATIONS */
    public function test_set_adds_value(): void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice']);
        $result = $arrh->set(key: 'age', value: 30);

        $this->assertSame(['name' => 'Alice', 'age' => 30], $result->all());
        $this->assertNotSame($arrh, $result);
    }

    public function test_set_supports_dot_notation(): void
    {
        $arrh   = $this->arrhae(items: []);
        $result = $arrh->set(key: 'user.name', value: 'Alice');

        $this->assertSame(['user' => ['name' => 'Alice']], $result->all());
    }

    public function test_set_creates_intermediate_arrays(): void
    {
        $arrh   = $this->arrhae(items: []);
        $result = $arrh->set(key: 'a.b.c', value: 'deep');

        $this->assertSame(['a' => ['b' => ['c' => 'deep']]], $result->all());
    }

    public function test_forget_removes_value(): void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice', 'age' => 30]);
        $result = $arrh->forget(key: 'name');

        $this->assertSame(['age' => 30], $result->all());
    }

    public function test_forget_supports_dot_notation(): void
    {
        $arrh = $this->arrhae(items: [
                                         'user' => ['name' => 'Alice', 'age' => 30],
                                     ]);
        $result = $arrh->forget(key: 'user.name');

        $this->assertSame(['user' => ['age' => 30]], $result->all());
    }

    public function test_add_appends_value(): void
    {
        $arrh   = $this->arrhae(items: [1, 2]);
        $result = $arrh->add(value: 3);

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function test_pull_removes_and_returns_value(): void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice']);
        $result = $arrh->pull(key: 'name');

        $this->assertSame('Alice', $result->first());
        $this->assertSame([], $result->second()->all());
        $this->assertSame(['name' => 'Alice'], $arrh->all());
    }

    public function test_merge_combines_arrays(): void
    {
        $arrh   = $this->arrhae(items: [1, 2]);
        $result = $arrh->merge(items: [3, 4]);

        $this->assertSame([1, 2, 3, 4], $result->all());
    }

    public function test_union_preserves_existing_keys(): void
    {
        $arrh   = $this->arrhae(items: [1 => 'one', 2 => 'two']);
        $result = $arrh->union(items: [2 => 'TWO', 3 => 'three']);

        $this->assertSame([1 => 'one', 2 => 'two', 3 => 'three'], $result->all());
    }

    /** TRANSFORM OPERATIONS */
    public function test_map_transforms_values(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3]);
        $result = $arrh->map(callback: static fn (int $n): int => $n * 2);

        $this->assertSame([2, 4, 6], $result->all());
    }

    public function test_filter_keeps_matching_values(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4]);
        $result = $arrh->filter(callback: static fn (int $n): bool => $n % 2 === 0);

        $this->assertSame([1 => 2, 3 => 4], $result->all());
    }

    public function test_reduce_aggregates_values(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4]);
        $result = $arrh->reduce(
            callback: static fn (int $carry, int $n): int => $carry + $n,
            initial : 0,
        );

        $this->assertSame(10, $result);
    }

    public function test_chunk_splits_array(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4, 5]);
        $result = $arrh->chunk(size: 2);

        $this->assertSame([[1, 2], [3, 4], [5 => 5]], $result->all());
    }

    /** SEARCH OPERATIONS */
    public function test_contains_finds_value(): void
    {
        $arrh = $this->arrhae(items: ['apple', 'banana', 'cherry']);

        $this->assertTrue($arrh->contains(value: 'banana'));
        $this->assertFalse($arrh->contains(value: 'date'));
    }

    public function test_search_returns_index(): void
    {
        $arrh = $this->arrhae(items: ['apple', 'banana', 'cherry']);

        $this->assertSame(1, $arrh->search(value: 'banana'));
        $this->assertFalse($arrh->search(value: 'date'));
    }

    public function test_where_filters_by_key(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['name' => 'Alice', 'active' => true],
                                         ['name' => 'Bob', 'active' => false],
                                     ]);
        $result = $arrh->where(key: 'active', value: true);

        $this->assertCount(1, $result->all());
        $this->assertSame('Alice', $result->first()['name']);
    }

    public function test_where_in_filters_by_key_in_array(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['name' => 'Alice', 'role' => 'admin'],
                                         ['name' => 'Bob', 'role' => 'editor'],
                                         ['name' => 'Charlie', 'role' => 'subscriber'],
                                     ]);
        $result = $arrh->whereIn(key: 'role', values: ['admin', 'editor']);

        $this->assertCount(2, $result->all());
    }

    public function test_where_between_filters_by_range(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['name' => 'Alice', 'score' => 85],
                                         ['name' => 'Bob', 'score' => 90],
                                         ['name' => 'Charlie', 'score' => 75],
                                     ]);
        $result = $arrh->whereBetween(key: 'score', range: [80, 90]);

        $this->assertCount(2, $result->all());
    }

    public function test_where_null_filters_null_values(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['name' => 'Alice', 'age' => null],
                                         ['name' => 'Bob', 'age' => 30],
                                     ]);
        $result = $arrh->whereNull(key: 'age');

        $this->assertCount(1, $result->all());
    }

    /** AGGREGATE OPERATIONS */
    public function test_sum_calculates_total(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['amount' => 100],
                                         ['amount' => 200],
                                         ['amount' => 150],
                                     ]);

        $this->assertSame(450, $arrh->sum(key: 'amount'));
    }

    public function test_average_calculates_mean(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['score' => 80],
                                         ['score' => 90],
                                         ['score' => 70],
                                     ]);

        $this->assertSameWithDelta(expected: 80.0, actual: $arrh->average(key: 'score'), delta: 0.01);
    }

    private function assertSameWithDelta(float $expected, float $actual, float $delta): void
    {
        $this->assertLessThanOrEqual($expected + $delta, $actual);
        $this->assertGreaterThanOrEqual($expected - $delta, $actual);
    }

    public function test_min_finds_minimum(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['score' => 80],
                                         ['score' => 90],
                                         ['score' => 70],
                                     ]);

        $this->assertSame(70, $arrh->min(key: 'score'));
    }

    public function test_max_finds_maximum(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['score' => 80],
                                         ['score' => 90],
                                         ['score' => 70],
                                     ]);

        $this->assertSame(90, $arrh->max(key: 'score'));
    }

    public function test_count_by_tallies_values(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['category' => 'A'],
                                         ['category' => 'B'],
                                         ['category' => 'A'],
                                     ]);

        $counts = $arrh->countBy(key: 'category');
        $this->assertSame(['A' => 2, 'B' => 1], $counts);
    }

    public function test_group_by_organizes_items(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['category' => 'A', 'name' => 'Alice'],
                                         ['category' => 'B', 'name' => 'Bob'],
                                         ['category' => 'A', 'name' => 'Charlie'],
                                     ]);

        $grouped = $arrh->aggregateGroupBy(key: 'category');
        $this->assertCount(2, $grouped['A']);
        $this->assertCount(1, $grouped['B']);
    }

    /** ORDER OPERATIONS */
    public function test_sort_orders_items(): void
    {
        $arrh   = $this->arrhae(items: [3, 1, 2]);
        $result = $arrh->sort();

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function test_sort_with_callback(): void
    {
        $arrh   = $this->arrhae(items: [3, 1, 2]);
        $result = $arrh->sort(callback: static fn (int $a, int $b): int => $b <=> $a);

        $this->assertSame([3, 2, 1], $result->all());
    }

    public function test_reverse_reverses_order(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3]);
        $result = $arrh->reverse();

        $this->assertSame([3, 2, 1], $result->all());
    }

    public function test_shuffle_randomizes_order(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4, 5]);
        $result = $arrh->shuffle();

        $this->assertCount(5, $result->all());
        $this->assertNotSame($arrh, $result);
    }

    public function test_unique_removes_duplicates(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 2, 3, 3, 3]);
        $result = $arrh->unique();

        $this->assertCount(3, $result->all());
    }

    public function test_key_by_reindexes_by_key(): void
    {
        $arrh = $this->arrhae(items: [
                                         ['id' => 'a', 'name' => 'Alice'],
                                         ['id' => 'b', 'name' => 'Bob'],
                                     ]);
        $result = $arrh->keyBy(key: 'id');

        $this->assertArrayHasKey('a', $result->all());
        $this->assertArrayHasKey('b', $result->all());
    }

    /** CONVERT OPERATIONS */
    public function test_to_json_encodes_to_json(): void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice', 'age' => 30]);
        $json = $arrh->toJson();

        $decoded = json_decode(json: $json, associative: true);
        $this->assertSame(['name' => 'Alice', 'age' => 30], $decoded);
    }

    public function test_to_array_normalizes_items(): void
    {
        $arrh  = $this->arrhae(items: [['name' => 'Alice'], ['name' => 'Bob']]);
        $array = $arrh->toArray();

        $this->assertCount(2, $array);
    }

    public function test_to_xml_encodes_to_xml(): void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice']);
        $xml  = $arrh->toXml(rootElement: 'user');

        $this->assertStringContainsString('<name>Alice</name>', $xml);
    }

    public function test_only_keeps_specified_keys(): void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice', 'age' => 30, 'city' => 'Wonderland']);
        $result = $arrh->only(keys: ['name', 'city']);

        $this->assertSame(['name' => 'Alice', 'city' => 'Wonderland'], $result->all());
    }

    public function test_except_removes_specified_keys(): void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice', 'age' => 30, 'city' => 'Wonderland']);
        $result = $arrh->except(keys: ['age']);

        $this->assertSame(['name' => 'Alice', 'city' => 'Wonderland'], $result->all());
    }

    /** LOCK OPERATIONS */
    public function test_lock_makes_collection_immutable(): void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice']);
        $locked = $arrh->lock();

        $this->assertTrue($locked->isLocked());
        $this->assertNotSame($arrh, $locked);
    }

    public function test_locked_collection_cannot_be_modified(): void
    {
        $arrh   = $this->arrhae(items: ['name' => 'Alice']);
        $locked = $arrh->lock();

        $this->expectException(RuntimeException::class);
        $locked->set(key: 'age', value: 30);
    }

    public function test_to_immutable_creates_locked_clone(): void
    {
        $arrh      = $this->arrhae(items: ['name' => 'Alice']);
        $immutable = $arrh->toImmutable();

        $this->assertTrue($immutable->isLocked());
    }

    /** STATIC FACTORIES */
    public function test_make_creates_instance(): void
    {
        $arrh = Arrhae::make(items: ['name' => 'Alice']);

        $this->assertSame(['name' => 'Alice'], $arrh->all());
    }

    public function test_make_handles_traversable(): void
    {
        $arrh = Arrhae::make(items: new ArrayIterator(array: ['name' => 'Alice']));

        $this->assertSame(['name' => 'Alice'], $arrh->all());
    }

    public function test_wrap_wraps_value(): void
    {
        $arrh = Arrhae::wrap(value: 'single');

        $this->assertSame(['single'], $arrh->all());
    }

    public function test_wrap_preserves_array(): void
    {
        $arrh = Arrhae::wrap(value: ['name' => 'Alice']);

        $this->assertSame(['name' => 'Alice'], $arrh->all());
    }

    public function test_wrap_preserves_instance(): void
    {
        $original = Arrhae::make(items: ['name' => 'Alice']);
        $wrapped  = Arrhae::wrap(value: $original);

        $this->assertSame($original, $wrapped);
    }

    /** INTERFACE COMPLIANCE */
    public function test_implements_array_access(): void
    {
        $arrh = $this->arrhae(items: ['name' => 'Alice']);

        $this->assertTrue(isset($arrh['name']));
        $this->assertSame('Alice', $arrh['name']);
    }

    public function test_implements_iterator_aggregate(): void
    {
        $arrh  = $this->arrhae(items: [1, 2, 3]);
        $items = [];

        foreach ($arrh as $item) {
            $items[] = $item;
        }

        $this->assertSame([1, 2, 3], $items);
    }

    public function test_implements_countable(): void
    {
        $arrh = $this->arrhae(items: [1, 2, 3]);

        $this->assertSame(3, $arrh->count());
    }

    /** HELPER METHODS */
    public function test_is_empty_returns_true_for_empty_collection(): void
    {
        $arrh = $this->arrhae(items: []);

        $this->assertTrue($arrh->isEmpty());
        $this->assertFalse($arrh->isNotEmpty());
    }

    public function test_is_not_empty_returns_true_for_non_empty_collection(): void
    {
        $arrh = $this->arrhae(items: [1]);

        $this->assertFalse($arrh->isEmpty());
        $this->assertTrue($arrh->isNotEmpty());
    }

    public function test_diff_returns_difference(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4]);
        $result = $arrh->diff(items: [2, 3]);

        $this->assertSame([1 => 1, 3 => 4], $result->all());
    }

    public function test_intersect_returns_intersection(): void
    {
        $arrh   = $this->arrhae(items: [1, 2, 3, 4]);
        $result = $arrh->intersect(items: [2, 3, 5]);

        $this->assertSame([1 => 2, 2 => 3], $result->all());
    }
}
