<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Aggregate\FindMaxValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Aggregate\FindMinValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Composites\Pair\Pair;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Convert\ConvertCollectionToArray;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Convert\ConvertCollectionToJson;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Convert\ConvertCollectionToXml;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Create\MakeCollection;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Create\WrapValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Exceptions\MutationException;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Mutability\MutationGuard;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Order\ReverseValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Order\ShuffleValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Order\SortValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Order\SortValuesBy;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Read\HasValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Read\ReadValueByPath;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Search\ContainsValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Search\SearchValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform\ChunkValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform\FilterValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform\FlipValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform\GroupValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform\MapValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform\PartitionValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform\ReduceValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform\UniqueValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\AppendValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\ForgetValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\PullValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\PutValueByPath;
use Traversable;

/**
 * Collection - fluent state owner for chainable array operations.
 * Recovered from legacy DataFoundation.
 */
final readonly class Collection implements CollectionInterface
{
    private MutationGuard $mutationGuard;

    public function __construct(
        private array $items = [],
    ) {
        $this->mutationGuard = new MutationGuard();
    }

    public static function make(iterable $items = []): static
    {
        return new self(items: new MakeCollection()->from(items: $items));
    }

    public static function wrap(mixed $value): static
    {
        return match (true) {
            $value instanceof static => $value,
            default => new self(items: new WrapValue()->intoArray(value: $value)),
        };
    }

    public function all(): array
    {
        return $this->items;
    }

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

        return reset(array: $this->items);
    }

    public function last(mixed $default = null): mixed
    {
        if ($this->items === []) {
            return $default;
        }

        return end(array: $this->items);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return $this->items[$key];
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return new ReadValueByPath(items: $this->items)->get(path: $key, default: $default);
        }

        return $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists(key: $key, array: $this->items)
            || new HasValue(items: $this->items)->check(key: $key);
    }

    public function set(string $key, mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        $items = $this->items;

        if (str_contains(haystack: $key, needle: '.')) {
            $items = new PutValueByPath(items: $items)->put(path: $key, value: $value);
        } else {
            $items[$key] = $value;
        }

        return new self(items: $items);
    }

    public function forget(string $key): static
    {
        $this->mutationGuard->assertMutable();

        return new self(
            items: new ForgetValue(items: $this->items)->forget(key: $key),
        );
    }

    public function add(mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        return new self(
            items: new AppendValue(items: $this->items)->append(value: $value),
        );
    }

    public function pull(string $key): Pair
    {
        $this->mutationGuard->assertMutable();

        [$value, $items] = new PullValue(items: $this->items)->pull(key: $key);

        return new Pair(
            first : $value,
            second: $items === $this->items ? $this : new self(items: $items),
        );
    }

    public function map(callable $callback): static
    {
        return new self(
            items: new MapValues(items: $this->items)->map(callback: $callback),
        );
    }

    public function filter(callable $callback): static
    {
        return new self(
            items: new FilterValues(items: $this->items)->filter(callback: $callback),
        );
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return new ReduceValues(items: $this->items)->reduce(callback: $callback, initial: $initial);
    }

    public function sum(string|callable $key): int|float
    {
        return new SumValues(items: $this->items)->sum(key: $key);
    }

    public function average(string|callable $key): float
    {
        return new AverageValues(items: $this->items)->average(key: $key);
    }

    public function min(string|callable $key): mixed
    {
        return new FindMinValue(items: $this->items)->min(key: $key);
    }

    public function max(string|callable $key): mixed
    {
        return new FindMaxValue(items: $this->items)->max(key: $key);
    }

    public function chunk(int $size): static
    {
        return new self(
            items: new ChunkValues(items: $this->items)->chunk(size: $size),
        );
    }

    public function groupBy(string|callable $key): array
    {
        return new GroupValues(items: $this->items)->group(key: $key);
    }

    public function keyBy(string|callable $key): static
    {
        $keyed = [];

        foreach ($this->items as $item) {
            $itemKey = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $keyed[$itemKey] = $item;
        }

        return new self(items: $keyed);
    }

    public function partition(callable $callback): array
    {
        [$pass, $fail] = new PartitionValues(items: $this->items)->partition(callback: $callback);

        return [new self(items: $pass), new self(items: $fail)];
    }

    public function contains(mixed $value): bool
    {
        return new ContainsValue(items: $this->items)->contains(value: $value);
    }

    public function search(mixed $value): int|false
    {
        return new SearchValue(items: $this->items)->search(value: $value);
    }

    public function whereIn(string $key, array $values): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item): bool => in_array(needle: $item[$key] ?? null, haystack: $values, strict: true),
        );

        return new self(items: $filtered);
    }

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

    public function where(string $key, mixed $value): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item): bool => ($item[$key] ?? null) === $value,
        );

        return new self(items: $filtered);
    }

    public function whereNotNull(string $key): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item): bool => ($item[$key] ?? null) !== null,
        );

        return new self(items: $filtered);
    }

    public function sort(?callable $callback = null) : static
    {
        return new self(
            items: new SortValues(items: $this->items)->sort(callback: $callback),
        );
    }

    public function sortBy(string|callable $key, bool $descending = false): static
    {
        return new self(
            items: new SortValuesBy(items: $this->items)->sortBy(key: $key, options: SORT_REGULAR, descending: $descending),
        );
    }

    public function reverse(): static
    {
        return new self(
            items: new ReverseValues(items: $this->items)->reverse(),
        );
    }

    public function shuffle(): static
    {
        return new self(
            items: new ShuffleValues(items: $this->items)->shuffle(),
        );
    }

    public function unique(): static
    {
        return new self(
            items: new UniqueValues(items: $this->items)->unique(),
        );
    }

    public function toArray(): array
    {
        return new ConvertCollectionToArray(items: $this->items)->toArray();
    }

    public function toJson(int $flags = 0): string
    {
        return new ConvertCollectionToJson(items: $this->items)->toJson(flags: $flags);
    }

    public function toXml(string $rootElement = 'root'): string
    {
        return new ConvertCollectionToXml(items: $this->items)->toXml(rootElement: $rootElement);
    }

    public function only(array $keys): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $_, mixed $key): bool => in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return new self(items: $filtered);
    }

    public function except(array $keys): static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $_, mixed $key): bool => ! in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return new self(items: $filtered);
    }

    public function pluck(string|callable $key): array
    {
        $plucked = [];

        foreach ($this->items as $item) {
            $plucked[] = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
        }

        return $plucked;
    }

    public function keys(): array
    {
        return array_keys(array: $this->items);
    }

    public function values(): static
    {
        return new self(items: array_values(array: $this->items));
    }

    public function flip(): static
    {
        return new self(
            items: new FlipValues(items: $this->items)->flip(),
        );
    }

    public function merge(array $items): static
    {
        return new self(items: array_merge($this->items, $items));
    }

    public function union(array $items): static
    {
        return new self(items: $this->items + $items);
    }

    public function diff(array $items): static
    {
        return new self(items: array_diff($this->items, $items));
    }

    public function intersect(array $items): static
    {
        return new self(items: array_intersect($this->items, $items));
    }

    public function tap(callable $callback): static
    {
        $callback($this);

        return $this;
    }

    public function unless(bool $condition, callable $callback): static
    {
        return $this->when(condition: ! $condition, callback: $callback);
    }

    public function when(bool $condition, callable $callback): static
    {
        if ($condition) {
            return $callback($this) ?? $this;
        }

        return $this;
    }

    public function toImmutable(): static
    {
        return $this->lock();
    }

    public function lock(): static
    {
        if ($this->mutationGuard->isLocked()) {
            throw MutationException::collectionIsAlreadyLocked();
        }

        $clone = new self(items: $this->items);
        $clone->mutationGuard->lock();

        return $clone;
    }

    public function isLocked(): bool
    {
        return $this->mutationGuard->isLocked();
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array: $this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists(key: (string) $offset, array: $this->items);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->mutationGuard->assertMutable();
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->mutationGuard->assertMutable();
    }
}
