<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;
use Avax\Components\DataStack\Data\System\Foundation\Failure\MutationException;
use Generator;
use PHPUnit\Framework\TestCase;
use stdClass;

final class CollectionTest extends TestCase
{
    // -- Factory Tests --

    public function test_make_from_empty_array() : void
    {
        $collection = Collection::make();

        $this->assertCount(0, $collection);
        $this->assertSame([], $collection->all());
    }

    public function test_make_from_array() : void
    {
        $collection = Collection::make([1, 2, 3]);

        $this->assertCount(3, $collection);
        $this->assertSame([1, 2, 3], $collection->all());
    }

    public function test_make_from_generator() : void
    {
        $generator = (static function () : Generator {
            yield 'a';
            yield 'b';
            yield 'c';
        })();

        $collection = Collection::make($generator);

        $this->assertCount(3, $collection);
        $this->assertSame(['a', 'b', 'c'], $collection->all());
    }

    public function test_make_preserves_keys() : void
    {
        $collection = Collection::make(['first' => 1, 'second' => 2]);

        $this->assertSame(['first' => 1, 'second' => 2], $collection->all());
    }

    public function test_wrap_collection_returns_same_instance() : void
    {
        $original = Collection::make([1, 2, 3]);
        $wrapped  = Collection::wrap($original);

        $this->assertSame($original, $wrapped);
    }

    public function test_wrap_non_collection_wraps_in_array() : void
    {
        $collection = Collection::wrap(42);

        $this->assertSame([42], $collection->all());
    }

    public function test_wrap_array_wraps_directly() : void
    {
        $collection = Collection::wrap(['a', 'b']);

        $this->assertSame(['a', 'b'], $collection->all());
    }

    // -- Access Tests --

    public function test_all_returns_items() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $collection->all());
    }

    public function test_count_returns_correct_count() : void
    {
        $collection = Collection::make([10, 20, 30]);

        $this->assertCount(3, $collection);
        $this->assertSame(3, $collection->count());
    }

    public function test_count_on_empty_collection() : void
    {
        $collection = Collection::make();

        $this->assertCount(0, $collection);
        $this->assertSame(0, $collection->count());
    }

    public function test_is_empty_returns_true_for_empty() : void
    {
        $collection = Collection::make();

        $this->assertTrue($collection->isEmpty());
    }

    public function test_is_empty_returns_false_for_non_empty() : void
    {
        $collection = Collection::make([1]);

        $this->assertFalse($collection->isEmpty());
    }

    public function test_is_not_empty_returns_true_for_non_empty() : void
    {
        $collection = Collection::make([1]);

        $this->assertTrue($collection->isNotEmpty());
    }

    public function test_is_not_empty_returns_false_for_empty() : void
    {
        $collection = Collection::make();

        $this->assertFalse($collection->isNotEmpty());
    }

    public function test_first_returns_first_element() : void
    {
        $collection = Collection::make(['a', 'b', 'c']);

        $this->assertSame('a', $collection->first());
    }

    public function test_first_returns_default_on_empty() : void
    {
        $collection = Collection::make();

        $this->assertSame('fallback', $collection->first('fallback'));
    }

    public function test_last_returns_last_element() : void
    {
        $collection = Collection::make(['a', 'b', 'c']);

        $this->assertSame('c', $collection->last());
    }

    public function test_last_returns_default_on_empty() : void
    {
        $collection = Collection::make();

        $this->assertSame('fallback', $collection->last('fallback'));
    }

    public function test_get_returns_value_by_key() : void
    {
        $collection = Collection::make(['name' => 'AvaX', 'version' => 4]);

        $this->assertSame('AvaX', $collection->get('name'));
        $this->assertSame(4, $collection->get('version'));
    }

    public function test_get_returns_default_for_missing_key() : void
    {
        $collection = Collection::make(['a' => 1]);

        $this->assertNull($collection->get('missing'));
        $this->assertSame('default', $collection->get('missing', 'default'));
    }

    public function test_get_supports_dot_notation() : void
    {
        $collection = Collection::make([
                                           'user' => ['name' => 'John', 'address' => ['city' => 'NYC']],
                                       ]);

        $this->assertSame('John', $collection->get('user.name'));
        $this->assertSame('NYC', $collection->get('user.address.city'));
    }

    public function test_has_returns_true_for_existing_key() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);

        $this->assertTrue($collection->has('a'));
        $this->assertTrue($collection->has('b'));
    }

    public function test_has_returns_false_for_missing_key() : void
    {
        $collection = Collection::make(['a' => 1]);

        $this->assertFalse($collection->has('c'));
    }

    public function test_has_supports_dot_notation() : void
    {
        $collection = Collection::make([
                                           'user' => ['profile' => ['age' => 30]],
                                       ]);

        $this->assertTrue($collection->has('user.profile.age'));
        $this->assertFalse($collection->has('user.profile.email'));
    }

    // -- Mutation Tests --

    public function test_set_returns_new_instance() : void
    {
        $original = Collection::make(['a' => 1]);
        $modified = $original->set('b', 2);

        $this->assertNotSame($original, $modified);
        $this->assertSame(['a' => 1], $original->all());
        $this->assertSame(['a' => 1, 'b' => 2], $modified->all());
    }

    public function test_set_overwrites_existing_key() : void
    {
        $collection = Collection::make(['a' => 1]);
        $modified   = $collection->set('a', 100);

        $this->assertSame(100, $modified->get('a'));
    }

    public function test_forget_returns_new_instance() : void
    {
        $original = Collection::make(['a' => 1, 'b' => 2]);
        $modified = $original->forget('a');

        $this->assertNotSame($original, $modified);
        $this->assertSame(['a' => 1, 'b' => 2], $original->all());
        $this->assertSame(['b' => 2], $modified->all());
    }

    public function test_forget_missing_key_returns_new_instance_unchanged() : void
    {
        $original = Collection::make(['a' => 1]);
        $modified = $original->forget('missing');

        $this->assertNotSame($original, $modified);
        $this->assertSame(['a' => 1], $modified->all());
    }

    public function test_add_appends_value() : void
    {
        $collection = Collection::make([1, 2]);
        $modified   = $collection->add(3);

        $this->assertSame([1, 2, 3], $modified->all());
    }

    public function test_pull_returns_value_and_remaining() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $pair       = $collection->pull('b');

        $this->assertSame(2, $pair->first());
        $this->assertInstanceOf(Collection::class, $pair->second());
        $this->assertSame(['a' => 1, 'c' => 3], $pair->second()->all());
    }

    public function test_pull_nested_returns_collection() : void
    {
        $collection = Collection::make([
                                           'user'  => ['name' => 'John', 'age' => 30],
                                           'other' => 'value',
                                       ]);
        $pair       = $collection->pull('user');

        $this->assertSame(['name' => 'John', 'age' => 30], $pair->first());
        $this->assertInstanceOf(Collection::class, $pair->second());
        // After pulling 'user', only 'other' remains
        $this->assertSame(['other' => 'value'], $pair->second()->all());
    }

    // -- Pipeline Tests --

    public function test_map_transforms_values() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $mapped     = $collection->map(static fn (int $item) : int => $item * 2);

        $this->assertSame([2, 4, 6], $mapped->all());
        $this->assertNotSame($collection, $mapped);
    }

    public function test_map_preserves_keys() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);
        $mapped     = $collection->map(static fn (int $item) : int => $item * 10);

        $this->assertSame(['a' => 10, 'b' => 20], $mapped->all());
    }

    public function test_filter_keeps_matching_items() : void
    {
        $collection = Collection::make([1, 2, 3, 4, 5]);
        $filtered   = $collection->filter(static fn (int $item) : bool => $item > 3);

        $this->assertSame([4, 5], array_values($filtered->all()));
    }

    public function test_filter_preserves_keys() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $filtered   = $collection->filter(static fn (int $item) : bool => $item % 2 === 1);

        $this->assertSame(['a' => 1, 'c' => 3], $filtered->all());
    }

    public function test_reduce_accumulates_values() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $sum = $collection->reduce(static fn (int $carry, int $item) : int => $carry + $item, 0);

        $this->assertSame(6, $sum);
    }

    public function test_reduce_with_initial_null() : void
    {
        $collection = Collection::make(['a', 'b', 'c']);
        $result     = $collection->reduce(static fn (?string $carry, string $item) : string => ($carry ?? '') . $item);

        $this->assertSame('abc', $result);
    }

    public function test_reject_removes_matching_items() : void
    {
        $collection = Collection::make([1, 2, 3, 4, 5]);
        $rejected   = $collection->reject(static fn (int $item) : bool => $item > 3);

        $this->assertSame([1, 2, 3], array_values($rejected->all()));
    }

    public function test_flatten_nested_arrays() : void
    {
        $collection = Collection::make([1, [2, 3], [4, [5, 6]]]);
        $flattened  = $collection->flatten();

        $this->assertSame([1, 2, 3, 4, 5, 6], array_values($flattened->all()));
    }

    public function test_flatten_with_depth() : void
    {
        $collection = Collection::make([1, [2, [3, [4]]]]);
        $flattened  = $collection->flatten(1);

        $this->assertSame([1, 2, [3, [4]]], array_values($flattened->all()));
    }

    public function test_each_iterates_values() : void
    {
        $items      = [];
        $collection = Collection::make([1, 2, 3]);
        $result     = $collection->each(static function (int $item) use (&$items) : void {
            $items[] = $item * 2;
        });

        $this->assertSame([2, 4, 6], $items);
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame([1, 2, 3], $result->all());
    }

    // -- Aggregate Tests --

    public function test_sum_numeric_values() : void
    {
        $collection = Collection::make([1, 2, 3, 4]);

        $this->assertSame(10, $collection->sum(static fn (int $item) : int => $item));
    }

    public function test_sum_by_property() : void
    {
        $collection = Collection::make([
                                           ['name' => 'A', 'price' => 10],
                                           ['name' => 'B', 'price' => 20],
                                           ['name' => 'C', 'price' => 30],
                                       ]);

        $this->assertSame(60, $collection->sum('price'));
    }

    public function test_average_by_property() : void
    {
        $collection = Collection::make([
                                           ['name' => 'A', 'score' => 10],
                                           ['name' => 'B', 'score' => 20],
                                           ['name' => 'C', 'score' => 30],
                                       ]);

        $this->assertSame(20.0, $collection->average('score'));
    }

    public function test_min_by_property() : void
    {
        $collection = Collection::make([
                                           ['name' => 'A', 'value' => 30],
                                           ['name' => 'B', 'value' => 10],
                                           ['name' => 'C', 'value' => 20],
                                       ]);

        $this->assertSame(10, $collection->min('value'));
    }

    public function test_max_by_property() : void
    {
        $collection = Collection::make([
                                           ['name' => 'A', 'value' => 30],
                                           ['name' => 'B', 'value' => 10],
                                           ['name' => 'C', 'value' => 20],
                                       ]);

        $this->assertSame(30, $collection->max('value'));
    }

    // -- Order Tests --

    public function test_sort_ascending() : void
    {
        $collection = Collection::make([3, 1, 2]);
        $sorted     = $collection->sort();

        $this->assertSame([1, 2, 3], array_values($sorted->all()));
    }

    public function test_sort_with_callback() : void
    {
        $collection = Collection::make([
                                           ['name' => 'C', 'order' => 3],
                                           ['name' => 'A', 'order' => 1],
                                           ['name' => 'B', 'order' => 2],
                                       ]);
        $sorted     = $collection->sort(static fn (array $a, array $b) : int => $a['order'] <=> $b['order']);

        $this->assertSame(['A', 'B', 'C'], $sorted->pluck('name'));
    }

    public function test_sort_by_property() : void
    {
        $collection = Collection::make([
                                           ['name' => 'C', 'score' => 30],
                                           ['name' => 'A', 'score' => 10],
                                           ['name' => 'B', 'score' => 20],
                                       ]);
        $sorted     = $collection->sortBy('score');

        $this->assertSame(['A', 'B', 'C'], $sorted->pluck('name'));
    }

    public function test_sort_by_descending() : void
    {
        $collection = Collection::make([
                                           ['name' => 'A', 'score' => 10],
                                           ['name' => 'B', 'score' => 30],
                                           ['name' => 'C', 'score' => 20],
                                       ]);
        $sorted     = $collection->sortBy('score', descending: true);

        $this->assertSame(['B', 'C', 'A'], $sorted->pluck('name'));
    }

    public function test_reverse() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $reversed   = $collection->reverse();

        $this->assertSame([3, 2, 1], array_values($reversed->all()));
    }

    public function test_shuffle_returns_same_items_different_order() : void
    {
        $collection = Collection::make([1, 2, 3, 4, 5]);
        $shuffled   = $collection->shuffle();

        $this->assertCount(5, $shuffled);
        $this->assertSame([1, 2, 3, 4, 5], $shuffled->sort()->all());
    }

    public function test_unique_removes_duplicates() : void
    {
        $collection = Collection::make([1, 2, 2, 3, 3, 3]);
        $unique     = $collection->unique();

        $this->assertSame([1, 2, 3], array_values($unique->all()));
    }

    // -- Grouping Tests --

    public function test_chunk_splits_into_groups() : void
    {
        $collection = Collection::make([1, 2, 3, 4, 5]);
        $chunked    = $collection->chunk(2);

        $expected   = [[1, 2], [3, 4], [5]];
        $chunkedAll = $chunked->all();
        foreach ($chunkedAll as $index => $chunk) {
            // Chunk returns Arrhae internally, convert to array for comparison
            $this->assertSame($expected[$index], array_values((array) $chunk));
        }
    }

    public function test_chunk_empty_collection() : void
    {
        $collection = Collection::make();
        $chunked    = $collection->chunk(3);

        $this->assertSame([], $chunked->all());
    }

    public function test_group_by_property() : void
    {
        $collection = Collection::make([
                                           ['type' => 'fruit', 'name' => 'apple'],
                                           ['type' => 'fruit', 'name' => 'banana'],
                                           ['type' => 'vegetable', 'name' => 'carrot'],
                                       ]);
        $grouped    = $collection->groupBy('type');

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped['fruit']);
        $this->assertCount(1, $grouped['vegetable']);
    }

    public function test_partition_splits_by_predicate() : void
    {
        $collection = Collection::make([1, 2, 3, 4, 5]);
        [$evens, $odds] = $collection->partition(static fn (int $item) : bool => $item % 2 === 0);

        $this->assertSame([2, 4], array_values($evens->all()));
        $this->assertSame([1, 3, 5], array_values($odds->all()));
    }

    // -- Search Tests --

    public function test_contains_value() : void
    {
        $collection = Collection::make(['a', 'b', 'c']);

        $this->assertTrue($collection->contains('a'));
        $this->assertTrue($collection->contains('b'));
        $this->assertFalse($collection->contains('z'));
    }

    public function test_search_finds_index() : void
    {
        $collection = Collection::make(['a', 'b', 'c']);

        $this->assertSame(0, $collection->search('a'));
        $this->assertSame(1, $collection->search('b'));
        $this->assertFalse($collection->search('z'));
    }

    // -- Selection Tests --

    public function test_only_keeps_specified_keys() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $only       = $collection->only(['a', 'c']);

        $this->assertSame(['a' => 1, 'c' => 3], $only->all());
    }

    public function test_only_ignores_missing_keys() : void
    {
        $collection = Collection::make(['a' => 1]);
        $only       = $collection->only(['a', 'z']);

        $this->assertSame(['a' => 1], $only->all());
    }

    public function test_except_removes_specified_keys() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $except     = $collection->except(['b']);

        $this->assertSame(['a' => 1, 'c' => 3], $except->all());
    }

    public function test_pluck_values_by_property() : void
    {
        $collection = Collection::make([
                                           ['name' => 'John', 'age' => 30],
                                           ['name' => 'Jane', 'age' => 25],
                                       ]);

        $this->assertSame(['John', 'Jane'], $collection->pluck('name'));
    }

    public function test_pluck_with_callable() : void
    {
        $collection = Collection::make([
                                           ['first' => 'John', 'last' => 'Doe'],
                                           ['first' => 'Jane', 'last' => 'Smith'],
                                       ]);

        $this->assertSame(
            ['John Doe', 'Jane Smith'],
            $collection->pluck(static fn (array $item) : string => $item['first'] . ' ' . $item['last']),
        );
    }

    public function test_pluck_returns_null_for_missing_property() : void
    {
        $collection = Collection::make([
                                           ['name' => 'John'],
                                           ['age' => 30],
                                       ]);

        $this->assertSame(['John', null], $collection->pluck('name'));
    }

    // -- Query / Filtering Tests --

    public function test_where_filters_by_value() : void
    {
        $collection = Collection::make([
                                           ['name' => 'John', 'active' => true],
                                           ['name' => 'Jane', 'active' => false],
                                           ['name' => 'Bob', 'active' => true],
                                       ]);

        $active = $collection->where('active', true);

        $this->assertCount(2, $active);
        $this->assertSame(['John', 'Bob'], $active->pluck('name'));
    }

    public function test_where_in_filters_by_list() : void
    {
        $collection = Collection::make([
                                           ['name' => 'John', 'role' => 'admin'],
                                           ['name' => 'Jane', 'role' => 'user'],
                                           ['name' => 'Bob', 'role' => 'moderator'],
                                       ]);

        $result = $collection->whereIn('role', ['admin', 'moderator']);

        $this->assertCount(2, $result);
        $this->assertSame(['John', 'Bob'], $result->pluck('name'));
    }

    public function test_where_between_filters_by_range() : void
    {
        $collection = Collection::make([
                                           ['name' => 'A', 'score' => 5],
                                           ['name' => 'B', 'score' => 15],
                                           ['name' => 'C', 'score' => 25],
                                           ['name' => 'D', 'score' => 35],
                                       ]);

        $result = $collection->whereBetween('score', [10, 30]);

        $this->assertCount(2, $result);
        $this->assertSame(['B', 'C'], $result->pluck('name'));
    }

    public function test_where_null_filters_null_values() : void
    {
        $collection = Collection::make([
                                           ['name' => 'John', 'email' => 'john@example.com'],
                                           ['name' => 'Jane', 'email' => null],
                                           ['name' => 'Bob', 'email' => 'bob@example.com'],
                                       ]);

        $result = $collection->whereNull('email');

        $this->assertCount(1, $result);
        $this->assertSame(['Jane'], $result->pluck('name'));
    }

    public function test_where_not_null_filters_non_null_values() : void
    {
        $collection = Collection::make([
                                           ['name' => 'John', 'email' => 'john@example.com'],
                                           ['name' => 'Jane', 'email' => null],
                                           ['name' => 'Bob', 'email' => 'bob@example.com'],
                                       ]);

        $result = $collection->whereNotNull('email');

        $this->assertCount(2, $result);
        $this->assertSame(['John', 'Bob'], $result->pluck('name'));
    }

    // -- Keying Tests --

    public function test_key_by_property() : void
    {
        $collection = Collection::make([
                                           ['id' => 'a', 'name' => 'John'],
                                           ['id' => 'b', 'name' => 'Jane'],
                                       ]);

        $keyed = $collection->keyBy('id');

        $this->assertSame(['a', 'b'], $keyed->keys());
        $this->assertSame('John', $keyed->get('a.name'));
    }

    public function test_key_by_callable() : void
    {
        $collection = Collection::make([
                                           ['id' => 1, 'name' => 'John'],
                                           ['id' => 2, 'name' => 'Jane'],
                                       ]);

        $keyed = $collection->keyBy(static fn (array $item) : string => 'user_' . $item['id']);

        $this->assertSame(['user_1', 'user_2'], $keyed->keys());
    }

    // -- Set Algebra Tests --

    public function test_flip_swaps_keys_and_values() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $flipped    = $collection->flip();

        $this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $flipped->all());
    }

    public function test_merge_combines_collections() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);
        $merged     = $collection->merge(['b' => 20, 'c' => 3]);

        $this->assertSame(['a' => 1, 'b' => 20, 'c' => 3], $merged->all());
    }

    public function test_union_combines_without_overwriting() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);
        $unioned    = $collection->union(['b' => 20, 'c' => 3]);

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $unioned->all());
    }

    public function test_diff_finds_differences() : void
    {
        $collection = Collection::make([1, 2, 3, 4]);
        $diff       = $collection->diff([2, 4]);

        $this->assertSame([0 => 1, 2 => 3], $diff->all());
    }

    public function test_intersect_finds_common_values() : void
    {
        $collection  = Collection::make([1, 2, 3, 4]);
        $intersected = $collection->intersect([2, 4, 6]);

        $this->assertSame([1 => 2, 3 => 4], $intersected->all());
    }

    // -- Info Tests --

    public function test_keys_returns_all_keys() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a', 'b', 'c'], $collection->keys());
    }

    public function test_values_reindexes_collection() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $values     = $collection->values();

        $this->assertSame([1, 2, 3], $values->all());
        $this->assertSame([0, 1, 2], $values->keys());
    }

    // -- Conditional / Pipeline Control Tests --

    public function test_tap_executes_callback() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $sideEffect = null;

        $result = $collection->tap(static function (Collection $col) use (&$sideEffect) : void {
            $sideEffect = $col->count();
        });

        $this->assertSame(3, $sideEffect);
        $this->assertSame($collection, $result);
    }

    public function test_when_executes_callback_on_true() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $result     = $collection->when(true, static fn (Collection $col) : Collection => $col->add(4));

        $this->assertSame([1, 2, 3, 4], $result->all());
    }

    public function test_when_skips_callback_on_false() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $result     = $collection->when(false, static fn (Collection $col) : Collection => $col->add(4));

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function test_unless_executes_callback_on_false() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $result     = $collection->unless(false, static fn (Collection $col) : Collection => $col->add(4));

        $this->assertSame([1, 2, 3, 4], $result->all());
    }

    public function test_unless_skips_callback_on_true() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $result     = $collection->unless(true, static fn (Collection $col) : Collection => $col->add(4));

        $this->assertSame([1, 2, 3], $result->all());
    }

    // -- Conversion Tests --

    public function test_to_array() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $collection->toArray());
    }

    public function test_to_json() : void
    {
        $collection = Collection::make(['name' => 'AvaX', 'version' => 4]);
        $json       = $collection->toJson();

        $this->assertJsonStringEqualsJsonString('{"name":"AvaX","version":4}', $json);
    }

    public function test_to_json_with_flags() : void
    {
        $collection = Collection::make(['name' => 'AvaX']);
        $json       = $collection->toJson(JSON_PRETTY_PRINT);

        $this->assertStringContainsString('"name"', $json);
        $this->assertStringContainsString('"AvaX"', $json);
    }

    public function test_to_xml() : void
    {
        $collection = Collection::make(['name' => 'AvaX', 'version' => 4]);
        $xml        = $collection->toXml();

        $this->assertStringContainsString('<root>', $xml);
        $this->assertStringContainsString('<name>AvaX</name>', $xml);
        $this->assertStringContainsString('<version>4</version>', $xml);
        $this->assertStringContainsString('</root>', $xml);
    }

    public function test_to_xml_custom_root() : void
    {
        $collection = Collection::make(['name' => 'AvaX']);
        $xml        = $collection->toXml('data');

        $this->assertStringContainsString('<data>', $xml);
        $this->assertStringContainsString('</data>', $xml);
    }

    // -- Immutability Tests --

    public function test_to_immutable_locks_collection() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $locked     = $collection->toImmutable();

        $this->assertTrue($locked->isLocked());
        $this->assertFalse($collection->isLocked());
    }

    public function test_lock_returns_locked_copy() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $locked     = $collection->lock();

        $this->assertTrue($locked->isLocked());
        $this->assertNotSame($collection, $locked);
    }

    public function test_locking_already_locked_throws() : void
    {
        $collection = Collection::make([1, 2, 3])->lock();

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Collection is already locked.');

        $collection->lock();
    }

    public function test_mutating_locked_collection_throws() : void
    {
        $collection = Collection::make([1, 2, 3])->lock();

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Value is locked');

        $collection->set('a', 1);
    }

    public function test_forget_on_locked_collection_throws() : void
    {
        $collection = Collection::make([1, 2, 3])->lock();

        $this->expectException(MutationException::class);

        $collection->forget('a');
    }

    public function test_add_on_locked_collection_throws() : void
    {
        $collection = Collection::make([1, 2, 3])->lock();

        $this->expectException(MutationException::class);

        $collection->add(4);
    }

    // -- IteratorAggregate Tests --

    public function test_foreach_iteration() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = [];

        foreach ($collection as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    // -- ArrayAccess Tests (read-only) --

    public function test_offset_exists() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);

        $this->assertTrue(isset($collection['a']));
        $this->assertFalse(isset($collection['z']));
    }

    public function test_offset_get() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);

        $this->assertSame(1, $collection['a']);
        $this->assertSame(2, $collection['b']);
        $this->assertNull($collection['missing']);
    }

    public function test_offset_set_throws() : void
    {
        $collection = Collection::make(['a' => 1]);

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Array-style mutation is not supported');

        $collection['b'] = 2;
    }

    public function test_offset_unset_throws() : void
    {
        $collection = Collection::make(['a' => 1, 'b' => 2]);

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Array-style mutation is not supported');

        unset($collection['a']);
    }

    // -- Edge Cases --

    public function test_collection_with_null_values() : void
    {
        $collection = Collection::make(['a' => null, 'b' => 'value', 'c' => null]);

        $this->assertNull($collection->get('a'));
        $this->assertTrue($collection->has('a'));
        $this->assertCount(3, $collection);
    }

    public function test_collection_with_objects() : void
    {
        $obj1       = new stdClass();
        $obj1->name = 'first';
        $obj2       = new stdClass();
        $obj2->name = 'second';

        $collection = Collection::make([$obj1, $obj2]);

        $this->assertSame($obj1, $collection->first());
        $this->assertSame($obj2, $collection->last());
    }

    public function test_chained_pipeline_operations() : void
    {
        $collection = Collection::make([
                                           ['name' => 'John', 'age' => 30, 'active' => true],
                                           ['name' => 'Jane', 'age' => 25, 'active' => false],
                                           ['name' => 'Bob', 'age' => 35, 'active' => true],
                                           ['name' => 'Alice', 'age' => 28, 'active' => true],
                                       ]);

        $result = $collection
            ->where('active', true)
            ->sortBy('age')
            ->pluck('name');

        // Jane filtered out, remaining sorted by age: Alice(28), John(30), Bob(35)
        $this->assertSame(['Alice', 'John', 'Bob'], $result);
    }

    public function test_collection_preserves_original_after_operations() : void
    {
        $original = Collection::make([1, 2, 3]);
        $modified = $original->map(static fn (int $item) : int => $item * 2);

        $this->assertSame([1, 2, 3], $original->all());
        $this->assertSame([2, 4, 6], $modified->all());
    }

    public function test_empty_collection_operations() : void
    {
        $collection = Collection::make();

        $this->assertSame([], $collection->map(static fn (int $item) : int => $item)->all());
        $this->assertSame([], $collection->filter(static fn (int $item) : bool => true)->all());
        $this->assertSame(0, $collection->reduce(static fn (int $carry) : int => $carry + 1, 0));
    }

    public function test_partition_returns_two_collections() : void
    {
        $collection = Collection::make([1, 2, 3, 4, 5]);
        [$pass, $fail] = $collection->partition(static fn (int $item) : bool => $item > 2);

        $this->assertInstanceOf(Collection::class, $pass);
        $this->assertInstanceOf(Collection::class, $fail);
        $this->assertSame([3, 4, 5], array_values($pass->all()));
        $this->assertSame([1, 2], array_values($fail->all()));
    }

    public function test_flatten_preserves_scalar_values() : void
    {
        $collection = Collection::make([1, [2, 3], 'four', [5, [6]]]);
        $flattened  = $collection->flatten();

        $this->assertSame([1, 2, 3, 'four', 5, 6], array_values($flattened->all()));
    }

    public function test_sort_by_callable() : void
    {
        $collection = Collection::make([
                                           ['name' => 'Charlie'],
                                           ['name' => 'Alice'],
                                           ['name' => 'Bob'],
                                       ]);

        $sorted = $collection->sortBy(static fn (array $item) : string => $item['name']);

        $this->assertSame(['Alice', 'Bob', 'Charlie'], $sorted->pluck('name'));
    }

    public function test_group_by_callable() : void
    {
        $collection = Collection::make([
                                           ['name' => 'John', 'role' => 'admin'],
                                           ['name' => 'Jane', 'role' => 'user'],
                                           ['name' => 'Bob', 'role' => 'admin'],
                                       ]);

        $grouped = $collection->groupBy(static fn (array $item) : string => $item['role']);

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped['admin']);
        $this->assertCount(1, $grouped['user']);
    }

    public function test_when_returns_self_when_callback_returns_null() : void
    {
        $collection = Collection::make([1, 2, 3]);
        $result     = $collection->when(true, static function () : void {});

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function test_collection_with_mixed_types() : void
    {
        $collection = Collection::make([
                                           'string' => 'hello',
                                           'int'    => 42,
                                           'float'  => 3.14,
                                           'bool'   => true,
                                           'null'   => null,
                                           'array'  => [1, 2, 3],
                                       ]);

        $this->assertSame('hello', $collection->get('string'));
        $this->assertSame(42, $collection->get('int'));
        $this->assertSame(3.14, $collection->get('float'));
        $this->assertTrue($collection->get('bool'));
        $this->assertNull($collection->get('null'));
        $this->assertSame([1, 2, 3], $collection->get('array'));
    }
}
