<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Arrhae;

use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\FindMaxValue;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\FindMinValue;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Capabilities\DataPaths\DotPath;
use Avax\Components\DataStack\Data\System\Foundation\Mutability\MutationGuard;
use Avax\Components\DataStack\Data\System\Foundation\Normalization\MakeCollection;
use Avax\Components\DataStack\Data\System\Foundation\Normalization\WrapValue;
use Avax\Components\DataStack\Data\System\Capabilities\Search\ContainsValue;
use Avax\Components\DataStack\Data\System\Capabilities\Search\SearchValue;
use Avax\Components\DataStack\Data\System\Capabilities\Selection\HasValue;
use Avax\Components\DataStack\Data\System\Capabilities\Selection\ReadValueByPath;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Pair;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\AppendValue;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ChunkValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ConvertCollectionToJson;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ConvertCollectionToXml;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\EachValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\FilterValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\FlattenValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\FlipValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\GroupValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\MapValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PartitionValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PullValue;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PutValueByPath;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ReduceValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\RejectValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ReverseValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ShuffleValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\SortValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\SortValuesBy;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\UniqueValues;
use NoDiscard;

/**
 * Arrhae — array-native data pipeline engine.
 *
 * The core array-backed DSL in AvaX Data.
 * Collection and Json compose Arrhae internally for shared pipeline behavior.
 *
 * Arrhae = Collection for arrays.
 * Same fluent pipeline power, native PHP array semantics.
 * Additionally owns dot-path access for nested array manipulation.
 */
final readonly class Arrhae
{
    private MutationGuard $mutationGuard;

    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
        $this->mutationGuard = new MutationGuard();
    }

    // -- Factories ---------------------------------------------------------------

    /** @param iterable<array-key, mixed> $items */
    public static function from(iterable $items): static
    {
        return self::make(items: $items);
    }

    /** @param iterable<array-key, mixed> $items */
    public static function make(iterable $items = []): static
    {
        return new self(items: (new MakeCollection())->from(items: $items));
    }

    public static function wrap(mixed $value): static
    {
        return match (true) {
            $value instanceof static => $value,
            default => new self(items: (new WrapValue())->intoArray(value: $value)),
        };
    }

    // -- Access ------------------------------------------------------------------

    /** @return array<array-key, mixed> */
    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return $this->items[$key];
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return (new ReadValueByPath(items: $this->items))->get(path: $key, default: $default);
        }

        return $default;
    }

    public function has(string $key): bool
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return true;
        }

        return (new HasValue(items: $this->items))->check(key: $key);
    }

    #[NoDiscard]
    public function set(string $key, mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        if (str_contains(haystack: $key, needle: '.')) {
            return new self(
                items: (new PutValueByPath(items: $this->items))->put(path: $key, value: $value),
            );
        }

        $items = $this->items;
        $items[$key] = $value;

        return new self(items: $items);
    }

    #[NoDiscard]
    public function forget(string $key): static
    {
        $this->mutationGuard->assertMutable();

        if (! array_key_exists(key: $key, array: $this->items) && str_contains(haystack: $key, needle: '.')) {
            $items = $this->items;
            (new DotPath(path: $key))->unsetValue(items: $items);

            return new self(items: $items);
        }

        $items = $this->items;
        unset($items[$key]);

        return new self(items: $items);
    }

    #[NoDiscard]
    public function add(mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        return new self(
            items: (new AppendValue(items: $this->items))->append(value: $value),
        );
    }

    // -- Info --------------------------------------------------------------------

    public function count(): int
    {
        return count(value: $this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function isNotEmpty(): bool
    {
        return $this->items !== [];
    }

    public function first(mixed $default = null): mixed
    {
        if ($this->items === []) {
            return $default;
        }

        $copy = $this->items;

        return reset(array: $copy);
    }

    public function last(mixed $default = null): mixed
    {
        if ($this->items === []) {
            return $default;
        }

        $copy = $this->items;

        return end(array: $copy);
    }

    // -- Pipeline ----------------------------------------------------------------

    #[NoDiscard]
    public function map(callable $callback): static
    {
        return new self(
            items: (new MapValues(items: $this->items))->map(callback: $callback),
        );
    }

    #[NoDiscard]
    public function filter(callable $callback): static
    {
        return new self(
            items: (new FilterValues(items: $this->items))->filter(callback: $callback),
        );
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return (new ReduceValues(items: $this->items))->reduce(callback: $callback, initial: $initial);
    }

    #[NoDiscard]
    public function reject(callable $callback): static
    {
        return new self(
            items: (new RejectValues(items: $this->items))->reject(callback: $callback),
        );
    }

    #[NoDiscard]
    public function flatten(int $depth = PHP_INT_MAX): static
    {
        return new self(
            items: (new FlattenValues(items: $this->items))->flatten(depth: max(1, $depth)),
        );
    }

    #[NoDiscard]
    public function each(callable $callback): static
    {
        return new self(
            items: (new EachValues(items: $this->items))->each(callback: $callback),
        );
    }

    // -- Aggregate ---------------------------------------------------------------

    public function sum(string|callable $key): int|float
    {
        return (new SumValues(items: $this->items))->sum(key: $key);
    }

    public function average(string|callable $key): float
    {
        return (new AverageValues(items: $this->items))->average(key: $key);
    }

    public function min(string|callable $key): mixed
    {
        return (new FindMinValue(items: $this->items))->min(key: $key);
    }

    public function max(string|callable $key): mixed
    {
        return (new FindMaxValue(items: $this->items))->max(key: $key);
    }

    // -- Order -------------------------------------------------------------------

    #[NoDiscard]
    public function sort(?callable $callback = null): static
    {
        return new self(
            items: (new SortValues(items: $this->items))->sort(callback: $callback),
        );
    }

    #[NoDiscard]
    public function sortBy(string|callable $key, bool $descending = false): static
    {
        return new self(
            items: (new SortValuesBy(items: $this->items))->sortBy(key: $key, options: SORT_REGULAR, descending: $descending),
        );
    }

    #[NoDiscard]
    public function reverse(): static
    {
        return new self(
            items: (new ReverseValues(items: $this->items))->reverse(),
        );
    }

    #[NoDiscard]
    public function shuffle(): static
    {
        return new self(
            items: (new ShuffleValues(items: $this->items))->shuffle(),
        );
    }

    #[NoDiscard]
    public function unique(): static
    {
        return new self(
            items: (new UniqueValues(items: $this->items))->unique(),
        );
    }

    // -- Grouping ----------------------------------------------------------------

    #[NoDiscard]
    public function chunk(int $size): static
    {
        return new self(
            items: (new ChunkValues(items: $this->items))->chunk(size: $size),
        );
    }

    /** @return array<array-key, list<mixed>> */
    public function groupBy(string|callable $key): array
    {
        return (new GroupValues(items: $this->items))->group(key: $key);
    }

    /**
     * @return array{static, static}
     */
    /** @return array{static, static} */
    public function partition(callable $callback): array
    {
        [$pass, $fail] = (new PartitionValues(items: $this->items))->partition(callback: $callback);

        return [new self(items: $pass), new self(items: $fail)];
    }

    // -- Search ------------------------------------------------------------------

    public function contains(mixed $value): bool
    {
        return (new ContainsValue(items: $this->items))->contains(value: $value);
    }

    public function search(mixed $value): int|false
    {
        return (new SearchValue(items: $this->items))->search(value: $value);
    }

    // -- Selection ---------------------------------------------------------------

    /**
     * @param list<int|string> $keys
     */
    public function only(array $keys): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $_, mixed $key): bool => in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return new self(items: $filtered);
    }

    /**
     * @param list<int|string> $keys
     */
    public function except(array $keys): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $_, mixed $key): bool => ! in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return new self(items: $filtered);
    }

    /**
     * @return list<mixed>
     */
    /** @return list<mixed> */
    public function pluck(string|callable $key): array
    {
        $plucked = [];

        foreach ($this->items as $item) {
            $plucked[] = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
        }

        return $plucked;
    }

    public function pull(string $key): Pair
    {
        $this->mutationGuard->assertMutable();

        [$value, $remaining] = (new PullValue(items: $this->items))->pull(key: $key);

        return new Pair(
            first : $value,
            second: $remaining === $this->items ? $this : new self(items: $remaining),
        );
    }

    // -- Query / Filtering -------------------------------------------------------

    public function where(string $key, mixed $value): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item): bool => ($item[$key] ?? null) === $value,
        );

        return new self(items: $filtered);
    }

    /**
     * @param list<mixed> $values
     */
    /** @param list<mixed> $values */
    public function whereIn(string $key, array $values): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item): bool => in_array(needle: $item[$key] ?? null, haystack: $values, strict: true),
        );

        return new self(items: $filtered);
    }

    /**
     * @param array{mixed, mixed} $range
     */
    /** @param array{mixed, mixed} $range */
    public function whereBetween(string $key, array $range): static
    {
        [$min, $max] = $range;

        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item): bool => ($item[$key] ?? null) >= $min && ($item[$key] ?? null) <= $max,
        );

        return new self(items: $filtered);
    }

    public function whereNull(string $key): static
    {
        return $this->where(key: $key, value: null);
    }

    public function whereNotNull(string $key): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item): bool => ($item[$key] ?? null) !== null,
        );

        return new self(items: $filtered);
    }

    // -- Keying ------------------------------------------------------------------

    public function keyBy(string|callable $key): static
    {
        $keyed = [];

        foreach ($this->items as $item) {
            $itemKey = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $keyed[$itemKey] = $item;
        }

        return new self(items: $keyed);
    }

    // -- Set Algebra -------------------------------------------------------------

    public function flip(): static
    {
        return new self(items: (new FlipValues(items: $this->items))->flip());
    }

    /**
     * @param array<array-key, mixed> $items
     */
    public function merge(array $items): static
    {
        return new self(items: array_merge($this->items, $items));
    }

    /**
     * @param array<array-key, mixed> $items
     */
    public function union(array $items): static
    {
        return new self(items: $this->items + $items);
    }

    /**
     * @param array<array-key, mixed> $items
     */
    public function diff(array $items): static
    {
        return new self(items: array_diff($this->items, $items));
    }

    /**
     * @param array<array-key, mixed> $items
     */
    public function intersect(array $items): static
    {
        return new self(items: array_intersect($this->items, $items));
    }

    // -- Info --------------------------------------------------------------------

    /**
     * @return list<array-key>
     */
    /** @return list<array-key> */
    public function keys(): array
    {
        return array_keys(array: $this->items);
    }

    public function values(): static
    {
        return new self(items: array_values(array: $this->items));
    }

    // -- Conditional / Pipeline Control ------------------------------------------

    public function tap(callable $callback): static
    {
        $callback($this);

        return $this;
    }

    public function when(bool $condition, callable $callback): static
    {
        if ($condition) {
            return $callback($this) ?? $this;
        }

        return $this;
    }

    public function unless(bool $condition, callable $callback): static
    {
        return $this->when(condition: ! $condition, callback: $callback);
    }

    // -- Conversion --------------------------------------------------------------

    /** @return array<array-key, mixed> */
    public function toArray(): array
    {
        return $this->items;
    }

    public function toJson(int $flags = 0): string
    {
        return (new ConvertCollectionToJson(items: $this->items))->toJson(flags: $flags);
    }

    public function toXml(string $rootElement = 'root'): string
    {
        return (new ConvertCollectionToXml(items: $this->items))->toXml(rootElement: $rootElement);
    }

    // -- Immutability ------------------------------------------------------------

    public function toImmutable(): static
    {
        return $this->lock();
    }

    public function isLocked(): bool
    {
        return $this->mutationGuard->isLocked();
    }

    public function lock(): static
    {
        $clone = new self(items: $this->items);
        $clone->mutationGuard->lock();

        return $clone;
    }
}
