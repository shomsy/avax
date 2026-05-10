<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\ArrayForm\Arrhae;
use Avax\Components\DataStack\Data\System\Foundation\Failure\MutationException;
use Generator;
use PHPUnit\Framework\TestCase;

final class ArrhaeTest extends TestCase
{
    // -- Factory Tests --

    public function test_make_from_empty_array() : void
    {
        $arrhae = Arrhae::make();

        $this->assertSame([], $arrhae->all());
        $this->assertSame(0, $arrhae->count());
    }

    public function test_make_from_array() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);

        $this->assertSame([1, 2, 3], $arrhae->all());
    }

    public function test_make_from_generator() : void
    {
        $generator = (static function () : Generator {
            yield 'a';
            yield 'b';
            yield 'c';
        })();

        $arrhae = Arrhae::make($generator);

        $this->assertSame(['a', 'b', 'c'], $arrhae->all());
    }

    public function test_make_preserves_keys() : void
    {
        $arrhae = Arrhae::make(['first' => 1, 'second' => 2]);

        $this->assertSame(['first' => 1, 'second' => 2], $arrhae->all());
    }

    public function test_from_is_alias_of_make() : void
    {
        $arrhae = Arrhae::from([1, 2, 3]);

        $this->assertSame([1, 2, 3], $arrhae->all());
    }

    public function test_wrap_existing_arrhae_returns_same() : void
    {
        $original = Arrhae::make([1, 2, 3]);
        $wrapped  = Arrhae::wrap($original);

        $this->assertSame($original, $wrapped);
    }

    public function test_wrap_array_creates_new_arrhae() : void
    {
        $arrhae = Arrhae::wrap(['a', 'b']);

        $this->assertSame(['a', 'b'], $arrhae->all());
    }

    public function test_wrap_scalar_wraps_in_array() : void
    {
        $arrhae = Arrhae::wrap(42);

        $this->assertSame([42], $arrhae->all());
    }

    // -- Access Tests --

    public function test_all_returns_items() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $arrhae->all());
    }

    public function test_get_returns_value_by_key() : void
    {
        $arrhae = Arrhae::make(['name' => 'AvaX', 'version' => 4]);

        $this->assertSame('AvaX', $arrhae->get('name'));
        $this->assertSame(4, $arrhae->get('version'));
    }

    public function test_get_returns_default_for_missing_key() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);

        $this->assertNull($arrhae->get('missing'));
        $this->assertSame('default', $arrhae->get('missing', 'default'));
    }

    public function test_get_supports_dot_notation() : void
    {
        $arrhae = Arrhae::make([
                                   'user' => ['name' => 'John', 'address' => ['city' => 'NYC']],
                               ]);

        $this->assertSame('John', $arrhae->get('user.name'));
        $this->assertSame('NYC', $arrhae->get('user.address.city'));
    }

    public function test_get_returns_default_for_missing_nested_path() : void
    {
        $arrhae = Arrhae::make(['user' => ['name' => 'John']]);

        $this->assertNull($arrhae->get('user.email'));
        $this->assertSame('unknown', $arrhae->get('user.email', 'unknown'));
    }

    public function test_has_returns_true_for_existing_key() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2]);

        $this->assertTrue($arrhae->has('a'));
        $this->assertTrue($arrhae->has('b'));
    }

    public function test_has_returns_false_for_missing_key() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);

        $this->assertFalse($arrhae->has('c'));
    }

    public function test_has_supports_dot_notation() : void
    {
        $arrhae = Arrhae::make([
                                   'user' => ['profile' => ['age' => 30]],
                               ]);

        $this->assertTrue($arrhae->has('user.profile.age'));
        $this->assertFalse($arrhae->has('user.profile.email'));
    }

    // -- Mutation Tests --

    public function test_set_returns_new_instance() : void
    {
        $original = Arrhae::make(['a' => 1]);
        $modified = $original->set('b', 2);

        $this->assertNotSame($original, $modified);
        $this->assertSame(['a' => 1], $original->all());
        $this->assertSame(['a' => 1, 'b' => 2], $modified->all());
    }

    public function test_set_overwrites_existing_key() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);
        $modified = $arrhae->set('a', 100);

        $this->assertSame(100, $modified->get('a'));
    }

    public function test_set_with_dot_notation() : void
    {
        $arrhae = Arrhae::make(['user' => ['name' => 'John']]);
        $modified = $arrhae->set('user.email', 'john@example.com');

        $this->assertSame('john@example.com', $modified->get('user.email'));
        $this->assertSame('John', $modified->get('user.name'));
    }

    public function test_set_creates_nested_path() : void
    {
        $arrhae   = Arrhae::make([]);
        $modified = $arrhae->set('user.profile.age', 30);

        $this->assertSame(30, $modified->get('user.profile.age'));
    }

    public function test_forget_returns_new_instance() : void
    {
        $original = Arrhae::make(['a' => 1, 'b' => 2]);
        $modified = $original->forget('a');

        $this->assertNotSame($original, $modified);
        $this->assertSame(['a' => 1, 'b' => 2], $original->all());
        $this->assertSame(['b' => 2], $modified->all());
    }

    public function test_forget_missing_key_returns_new_instance() : void
    {
        $original = Arrhae::make(['a' => 1]);
        $modified = $original->forget('missing');

        $this->assertNotSame($original, $modified);
        $this->assertSame(['a' => 1], $modified->all());
    }

    public function test_forget_with_dot_notation() : void
    {
        $arrhae   = Arrhae::make(['user' => ['name' => 'John', 'email' => 'john@example.com']]);
        $modified = $arrhae->forget('user.email');

        $this->assertNull($modified->get('user.email'));
        $this->assertSame('John', $modified->get('user.name'));
    }

    public function test_add_appends_value() : void
    {
        $arrhae = Arrhae::make([1, 2]);
        $modified = $arrhae->add(3);

        $this->assertSame([1, 2, 3], $modified->all());
    }

    public function test_pull_returns_value_and_remaining() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $pair   = $arrhae->pull('b');

        $this->assertSame(2, $pair->first());
        $this->assertInstanceOf(Arrhae::class, $pair->second());
        $this->assertSame(['a' => 1, 'c' => 3], $pair->second()->all());
    }

    public function test_pull_missing_key_returns_null_and_original() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);
        $pair = $arrhae->pull('missing');

        $this->assertNull($pair->first());
        $this->assertSame(['a' => 1], $pair->second()->all());
    }

    // -- Info Tests --

    public function test_count() : void
    {
        $arrhae = Arrhae::make([10, 20, 30]);

        $this->assertSame(3, $arrhae->count());
    }

    public function test_is_empty() : void
    {
        $empty    = Arrhae::make();
        $nonEmpty = Arrhae::make([1]);

        $this->assertTrue($empty->isEmpty());
        $this->assertFalse($nonEmpty->isEmpty());
    }

    public function test_is_not_empty() : void
    {
        $empty    = Arrhae::make();
        $nonEmpty = Arrhae::make([1]);

        $this->assertFalse($empty->isNotEmpty());
        $this->assertTrue($nonEmpty->isNotEmpty());
    }

    public function test_first_returns_first_element() : void
    {
        $arrhae = Arrhae::make(['a', 'b', 'c']);

        $this->assertSame('a', $arrhae->first());
    }

    public function test_first_returns_default_on_empty() : void
    {
        $arrhae = Arrhae::make();

        $this->assertSame('fallback', $arrhae->first('fallback'));
    }

    public function test_last_returns_last_element() : void
    {
        $arrhae = Arrhae::make(['a', 'b', 'c']);

        $this->assertSame('c', $arrhae->last());
    }

    public function test_last_returns_default_on_empty() : void
    {
        $arrhae = Arrhae::make();

        $this->assertSame('fallback', $arrhae->last('fallback'));
    }

    // -- Pipeline Tests --

    public function test_map_transforms_values() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $mapped = $arrhae->map(static fn (int $item) : int => $item * 2);

        $this->assertSame([2, 4, 6], $mapped->all());
    }

    public function test_map_preserves_keys() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2]);
        $mapped = $arrhae->map(static fn (int $item) : int => $item * 10);

        $this->assertSame(['a' => 10, 'b' => 20], $mapped->all());
    }

    public function test_filter_keeps_matching_items() : void
    {
        $arrhae   = Arrhae::make([1, 2, 3, 4, 5]);
        $filtered = $arrhae->filter(static fn (int $item) : bool => $item > 3);

        $this->assertSame([4, 5], array_values($filtered->all()));
    }

    public function test_filter_preserves_keys() : void
    {
        $arrhae   = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $filtered = $arrhae->filter(static fn (int $item) : bool => $item % 2 === 1);

        $this->assertSame(['a' => 1, 'c' => 3], $filtered->all());
    }

    public function test_reduce_accumulates_values() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $sum    = $arrhae->reduce(static fn (int $carry, int $item) : int => $carry + $item, 0);

        $this->assertSame(6, $sum);
    }

    public function test_reject_removes_matching_items() : void
    {
        $arrhae   = Arrhae::make([1, 2, 3, 4, 5]);
        $rejected = $arrhae->reject(static fn (int $item) : bool => $item > 3);

        $this->assertSame([1, 2, 3], array_values($rejected->all()));
    }

    public function test_flatten_nested_arrays() : void
    {
        $arrhae    = Arrhae::make([1, [2, 3], [4, [5, 6]]]);
        $flattened = $arrhae->flatten();

        $this->assertSame([1, 2, 3, 4, 5, 6], array_values($flattened->all()));
    }

    public function test_flatten_with_depth() : void
    {
        $arrhae    = Arrhae::make([1, [2, [3, [4]]]]);
        $flattened = $arrhae->flatten(1);

        $this->assertSame([1, 2, [3, [4]]], array_values($flattened->all()));
    }

    public function test_flatten_minimum_depth_is_1() : void
    {
        $arrhae    = Arrhae::make([1, [2, 3]]);
        $flattened = $arrhae->flatten(0);

        $this->assertSame([1, 2, 3], array_values($flattened->all()));
    }

    public function test_each_iterates_values() : void
    {
        $items  = [];
        $arrhae = Arrhae::make([1, 2, 3]);
        $result = $arrhae->each(static function (int $item) use (&$items) : void {
            $items[] = $item * 2;
        });

        $this->assertSame([2, 4, 6], $items);
        $this->assertSame([1, 2, 3], $result->all());
    }

    // -- Aggregate Tests --

    public function test_sum_by_property() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'A', 'price' => 10],
                                   ['name' => 'B', 'price' => 20],
                                   ['name' => 'C', 'price' => 30],
                               ]);

        $this->assertSame(60, $arrhae->sum('price'));
    }

    public function test_sum_by_callable() : void
    {
        $arrhae = Arrhae::make([1, 2, 3, 4]);

        $this->assertSame(10, $arrhae->sum(static fn (int $item) : int => $item));
    }

    public function test_average_by_property() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'A', 'score' => 10],
                                   ['name' => 'B', 'score' => 20],
                                   ['name' => 'C', 'score' => 30],
                               ]);

        $this->assertSame(20.0, $arrhae->average('score'));
    }

    public function test_min_by_property() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'A', 'value' => 30],
                                   ['name' => 'B', 'value' => 10],
                                   ['name' => 'C', 'value' => 20],
                               ]);

        $this->assertSame(10, $arrhae->min('value'));
    }

    public function test_max_by_property() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'A', 'value' => 30],
                                   ['name' => 'B', 'value' => 10],
                                   ['name' => 'C', 'value' => 20],
                               ]);

        $this->assertSame(30, $arrhae->max('value'));
    }

    // -- Order Tests --

    public function test_sort_ascending() : void
    {
        $arrhae = Arrhae::make([3, 1, 2]);
        $sorted = $arrhae->sort();

        $this->assertSame([1, 2, 3], array_values($sorted->all()));
    }

    public function test_sort_with_callback() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'C', 'order' => 3],
                                   ['name' => 'A', 'order' => 1],
                                   ['name' => 'B', 'order' => 2],
                               ]);
        $sorted = $arrhae->sort(static fn (array $a, array $b) : int => $a['order'] <=> $b['order']);

        $this->assertSame(['A', 'B', 'C'], $sorted->pluck('name'));
    }

    public function test_sort_by_property() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'C', 'score' => 30],
                                   ['name' => 'A', 'score' => 10],
                                   ['name' => 'B', 'score' => 20],
                               ]);
        $sorted = $arrhae->sortBy('score');

        $this->assertSame(['A', 'B', 'C'], $sorted->pluck('name'));
    }

    public function test_sort_by_descending() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'A', 'score' => 10],
                                   ['name' => 'B', 'score' => 30],
                                   ['name' => 'C', 'score' => 20],
                               ]);
        $sorted = $arrhae->sortBy('score', descending: true);

        $this->assertSame(['B', 'C', 'A'], $sorted->pluck('name'));
    }

    public function test_reverse() : void
    {
        $arrhae   = Arrhae::make([1, 2, 3]);
        $reversed = $arrhae->reverse();

        $this->assertSame([3, 2, 1], array_values($reversed->all()));
    }

    public function test_shuffle_preserves_count() : void
    {
        $arrhae   = Arrhae::make([1, 2, 3, 4, 5]);
        $shuffled = $arrhae->shuffle();

        $this->assertSame(5, $shuffled->count());
    }

    public function test_unique_removes_duplicates() : void
    {
        $arrhae = Arrhae::make([1, 2, 2, 3, 3, 3]);
        $unique = $arrhae->unique();

        $this->assertSame([1, 2, 3], array_values($unique->all()));
    }

    // -- Grouping Tests --

    public function test_chunk_splits_into_groups() : void
    {
        $arrhae  = Arrhae::make([1, 2, 3, 4, 5]);
        $chunked = $arrhae->chunk(2);

        $expected = [[1, 2], [3, 4], [5]];
        $this->assertSame(3, $chunked->count());
        foreach ($chunked->all() as $index => $chunk) {
            // array_chunk returns plain arrays
            $this->assertIsArray($chunk);
            $this->assertSame($expected[$index], array_values($chunk));
        }
    }

    public function test_chunk_empty() : void
    {
        $arrhae  = Arrhae::make();
        $chunked = $arrhae->chunk(3);

        $this->assertSame(0, $arrhae->count());
        $this->assertSame([], $chunked->all());
    }

    public function test_group_by_property() : void
    {
        $arrhae  = Arrhae::make([
                                    ['type' => 'fruit', 'name' => 'apple'],
                                    ['type' => 'fruit', 'name' => 'banana'],
                                    ['type' => 'vegetable', 'name' => 'carrot'],
                                ]);
        $grouped = $arrhae->groupBy('type');

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped['fruit']);
        $this->assertCount(1, $grouped['vegetable']);
    }

    public function test_group_by_callable() : void
    {
        $arrhae  = Arrhae::make([
                                    ['name' => 'John', 'role' => 'admin'],
                                    ['name' => 'Jane', 'role' => 'user'],
                                    ['name' => 'Bob', 'role' => 'admin'],
                                ]);
        $grouped = $arrhae->groupBy(static fn (array $item) : string => $item['role']);

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped['admin']);
        $this->assertCount(1, $grouped['user']);
    }

    public function test_partition_splits_by_predicate() : void
    {
        $arrhae = Arrhae::make([1, 2, 3, 4, 5]);
        [$pass, $fail] = $arrhae->partition(static fn (int $item) : bool => $item > 2);

        $this->assertSame([3, 4, 5], array_values($pass->all()));
        $this->assertSame([1, 2], array_values($fail->all()));
    }

    // -- Search Tests --

    public function test_contains_value() : void
    {
        $arrhae = Arrhae::make(['a', 'b', 'c']);

        $this->assertTrue($arrhae->contains('a'));
        $this->assertFalse($arrhae->contains('z'));
    }

    public function test_search_finds_index() : void
    {
        $arrhae = Arrhae::make(['a', 'b', 'c']);

        $this->assertSame(0, $arrhae->search('a'));
        $this->assertSame(1, $arrhae->search('b'));
        $this->assertFalse($arrhae->search('z'));
    }

    // -- Selection Tests --

    public function test_only_keeps_specified_keys() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $only = $arrhae->only(['a', 'c']);

        $this->assertSame(['a' => 1, 'c' => 3], $only->all());
    }

    public function test_only_ignores_missing_keys() : void
    {
        $arrhae = Arrhae::make(['a' => 1]);
        $only   = $arrhae->only(['a', 'z']);

        $this->assertSame(['a' => 1], $only->all());
    }

    public function test_except_removes_specified_keys() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $except = $arrhae->except(['b']);

        $this->assertSame(['a' => 1, 'c' => 3], $except->all());
    }

    public function test_pluck_values_by_property() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'John', 'age' => 30],
                                   ['name' => 'Jane', 'age' => 25],
                               ]);

        $this->assertSame(['John', 'Jane'], $arrhae->pluck('name'));
    }

    public function test_pluck_with_callable() : void
    {
        $arrhae = Arrhae::make([
                                   ['first' => 'John', 'last' => 'Doe'],
                                   ['first' => 'Jane', 'last' => 'Smith'],
                               ]);

        $this->assertSame(
            ['John Doe', 'Jane Smith'],
            $arrhae->pluck(static fn (array $item) : string => $item['first'] . ' ' . $item['last']),
        );
    }

    public function test_pluck_returns_null_for_missing() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'John'],
                                   ['age' => 30],
                               ]);

        $this->assertSame(['John', null], $arrhae->pluck('name'));
    }

    // -- Query / Filtering Tests --

    public function test_where_filters_by_value() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'John', 'active' => true],
                                   ['name' => 'Jane', 'active' => false],
                                   ['name' => 'Bob', 'active' => true],
                               ]);

        $active = $arrhae->where('active', true);

        $this->assertSame(2, $active->count());
        $this->assertSame(['John', 'Bob'], $active->pluck('name'));
    }

    public function test_where_in_filters_by_list() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'John', 'role' => 'admin'],
                                   ['name' => 'Jane', 'role' => 'user'],
                                   ['name' => 'Bob', 'role' => 'moderator'],
                               ]);

        $result = $arrhae->whereIn('role', ['admin', 'moderator']);

        $this->assertSame(2, $result->count());
        $this->assertSame(['John', 'Bob'], $result->pluck('name'));
    }

    public function test_where_between_filters_by_range() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'A', 'score' => 5],
                                   ['name' => 'B', 'score' => 15],
                                   ['name' => 'C', 'score' => 25],
                                   ['name' => 'D', 'score' => 35],
                               ]);

        $result = $arrhae->whereBetween('score', [10, 30]);

        $this->assertSame(2, $result->count());
        $this->assertSame(['B', 'C'], $result->pluck('name'));
    }

    public function test_where_null_filters_null_values() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'John', 'email' => 'john@example.com'],
                                   ['name' => 'Jane', 'email' => null],
                                   ['name' => 'Bob', 'email' => 'bob@example.com'],
                               ]);

        $result = $arrhae->whereNull('email');

        $this->assertSame(1, $result->count());
        $this->assertSame(['Jane'], $result->pluck('name'));
    }

    public function test_where_not_null_filters_non_null_values() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'John', 'email' => 'john@example.com'],
                                   ['name' => 'Jane', 'email' => null],
                               ]);

        $result = $arrhae->whereNotNull('email');

        $this->assertSame(1, $result->count());
        $this->assertSame(['John'], $result->pluck('name'));
    }

    // -- Keying Tests --

    public function test_key_by_property() : void
    {
        $arrhae = Arrhae::make([
                                   ['id' => 'a', 'name' => 'John'],
                                   ['id' => 'b', 'name' => 'Jane'],
                               ]);

        $keyed = $arrhae->keyBy('id');

        $this->assertSame(['a', 'b'], $keyed->keys());
        $this->assertSame('John', $keyed->get('a.name'));
    }

    public function test_key_by_callable() : void
    {
        $arrhae = Arrhae::make([
                                   ['id' => 1, 'name' => 'John'],
                                   ['id' => 2, 'name' => 'Jane'],
                               ]);

        $keyed = $arrhae->keyBy(static fn (array $item) : string => 'user_' . $item['id']);

        $this->assertSame(['user_1', 'user_2'], $keyed->keys());
    }

    // -- Set Algebra Tests --

    public function test_flip_swaps_keys_and_values() : void
    {
        $arrhae  = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $flipped = $arrhae->flip();

        $this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $flipped->all());
    }

    public function test_merge_combines() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2]);
        $merged = $arrhae->merge(['b' => 20, 'c' => 3]);

        $this->assertSame(['a' => 1, 'b' => 20, 'c' => 3], $merged->all());
    }

    public function test_union_without_overwriting() : void
    {
        $arrhae  = Arrhae::make(['a' => 1, 'b' => 2]);
        $unioned = $arrhae->union(['b' => 20, 'c' => 3]);

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $unioned->all());
    }

    public function test_diff_finds_differences() : void
    {
        $arrhae = Arrhae::make([1, 2, 3, 4]);
        $diff   = $arrhae->diff([2, 4]);

        $this->assertSame([0 => 1, 2 => 3], $diff->all());
    }

    public function test_intersect_finds_common_values() : void
    {
        $arrhae      = Arrhae::make([1, 2, 3, 4]);
        $intersected = $arrhae->intersect([2, 4, 6]);

        $this->assertSame([1 => 2, 3 => 4], $intersected->all());
    }

    // -- Info Tests --

    public function test_keys() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a', 'b', 'c'], $arrhae->keys());
    }

    public function test_values_reindexes() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);
        $values = $arrhae->values();

        $this->assertSame([1, 2, 3], $values->all());
        $this->assertSame([0, 1, 2], $values->keys());
    }

    // -- Conditional / Pipeline Control Tests --

    public function test_tap_executes_callback() : void
    {
        $arrhae     = Arrhae::make([1, 2, 3]);
        $sideEffect = null;

        $result = $arrhae->tap(static function (Arrhae $a) use (&$sideEffect) : void {
            $sideEffect = $a->count();
        });

        $this->assertSame(3, $sideEffect);
        $this->assertSame($arrhae, $result);
    }

    public function test_when_executes_on_true() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $result = $arrhae->when(true, static fn (Arrhae $a) : Arrhae => $a->add(4));

        $this->assertSame([1, 2, 3, 4], $result->all());
    }

    public function test_when_skips_on_false() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $result = $arrhae->when(false, static fn (Arrhae $a) : Arrhae => $a->add(4));

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function test_unless_executes_on_false() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $result = $arrhae->unless(false, static fn (Arrhae $a) : Arrhae => $a->add(4));

        $this->assertSame([1, 2, 3, 4], $result->all());
    }

    public function test_unless_skips_on_true() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $result = $arrhae->unless(true, static fn (Arrhae $a) : Arrhae => $a->add(4));

        $this->assertSame([1, 2, 3], $result->all());
    }

    // -- Conversion Tests --

    public function test_to_array() : void
    {
        $arrhae = Arrhae::make(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $arrhae->toArray());
    }

    public function test_to_json() : void
    {
        $arrhae = Arrhae::make(['name' => 'AvaX', 'version' => 4]);
        $json   = $arrhae->toJson();

        $this->assertJsonStringEqualsJsonString('{"name":"AvaX","version":4}', $json);
    }

    public function test_to_xml() : void
    {
        $arrhae = Arrhae::make(['name' => 'AvaX', 'version' => 4]);
        $xml    = $arrhae->toXml();

        $this->assertStringContainsString('<root>', $xml);
        $this->assertStringContainsString('<name>AvaX</name>', $xml);
        $this->assertStringContainsString('<version>4</version>', $xml);
    }

    public function test_to_xml_custom_root() : void
    {
        $arrhae = Arrhae::make(['name' => 'AvaX']);
        $xml = $arrhae->toXml('data');

        $this->assertStringContainsString('<data>', $xml);
        $this->assertStringContainsString('</data>', $xml);
    }

    // -- Immutability Tests --

    public function test_to_immutable_locks() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $locked = $arrhae->toImmutable();

        $this->assertTrue($locked->isLocked());
    }

    public function test_lock_returns_locked_copy() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $locked = $arrhae->lock();

        $this->assertTrue($locked->isLocked());
        $this->assertNotSame($arrhae, $locked);
    }

    public function test_locking_twice_returns_fresh_locked_copy() : void
    {
        $locked       = Arrhae::make([1, 2, 3])->lock();
        $doubleLocked = $locked->lock();

        $this->assertTrue($doubleLocked->isLocked());
        $this->assertNotSame($locked, $doubleLocked);
    }

    public function test_set_on_locked_throws() : void
    {
        $arrhae = Arrhae::make([1, 2, 3])->lock();

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Value is locked');

        (void) $arrhae->set('a', 1);
    }

    public function test_forget_on_locked_throws() : void
    {
        $arrhae = Arrhae::make([1, 2, 3])->lock();

        $this->expectException(MutationException::class);

        (void) $arrhae->forget('a');
    }

    public function test_add_on_locked_throws() : void
    {
        $arrhae = Arrhae::make([1, 2, 3])->lock();

        $this->expectException(MutationException::class);

        (void) $arrhae->add(4);
    }

    public function test_pull_on_locked_throws() : void
    {
        $arrhae = Arrhae::make(['a' => 1])->lock();

        $this->expectException(MutationException::class);

        $arrhae->pull('a');
    }

    // -- Edge Cases --

    public function test_preserves_original_after_operations() : void
    {
        $original = Arrhae::make([1, 2, 3]);
        $modified = $original->map(static fn (int $item) : int => $item * 2);

        $this->assertSame([1, 2, 3], $original->all());
        $this->assertSame([2, 4, 6], $modified->all());
    }

    public function test_empty_operations() : void
    {
        $arrhae = Arrhae::make();

        $this->assertSame([], $arrhae->map(static fn (int $item) : int => $item)->all());
        $this->assertSame([], $arrhae->filter(static fn (int $item) : bool => true)->all());
        $this->assertSame(0, $arrhae->reduce(static fn (int $carry) : int => $carry + 1, 0));
    }

    public function test_sort_by_callable() : void
    {
        $arrhae = Arrhae::make([
                                   ['name' => 'Charlie'],
                                   ['name' => 'Alice'],
                                   ['name' => 'Bob'],
                               ]);

        $sorted = $arrhae->sortBy(static fn (array $item) : string => $item['name']);

        $this->assertSame(['Alice', 'Bob', 'Charlie'], $sorted->pluck('name'));
    }

    public function test_when_returns_self_on_null_callback_result() : void
    {
        $arrhae = Arrhae::make([1, 2, 3]);
        $result = $arrhae->when(true, static function () : void {});

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function test_mixed_types() : void
    {
        $arrhae = Arrhae::make([
                                   'string' => 'hello',
                                   'int'    => 42,
                                   'float'  => 3.14,
                                   'bool'   => true,
                                   'null'   => null,
                               ]);

        $this->assertSame('hello', $arrhae->get('string'));
        $this->assertSame(42, $arrhae->get('int'));
        $this->assertSame(3.14, $arrhae->get('float'));
        $this->assertTrue($arrhae->get('bool'));
        $this->assertNull($arrhae->get('null'));
    }
}
