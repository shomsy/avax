<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Arrhae;

use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\FindMaxValue;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\FindMinValue;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\Pair;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ConvertCollectionToJson;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ConvertCollectionToXml;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\MakeCollection;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\WrapValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\SharedCollectionBehavior;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\MutationGuard;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\DotPath;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ReverseValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ShuffleValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\SortValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\SortValuesBy;
use Avax\Components\DataStack\Data\System\Capabilities\Selection\HasValue;
use Avax\Components\DataStack\Data\System\Capabilities\Selection\ReadValueByPath;
use Avax\Components\DataStack\Data\System\Capabilities\Search\ContainsValue;
use Avax\Components\DataStack\Data\System\Capabilities\Search\SearchValue;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ChunkValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\EachValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\FilterValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\FlattenValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\GroupValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\MapValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PartitionValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ReduceValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\RejectValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\UniqueValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\AppendValue;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PullValue;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PutValueByPath;
use NoDiscard;

/**
 * Arrhae — raw array facade for simple array manipulation.
 *
 * Shares the same method vocabulary as Collection and Json
 * for consistent data manipulation across the AvaX data DSL.
 */
final readonly class Arrhae
{
    use SharedCollectionBehavior;

    private MutationGuard $mutationGuard;

    public function __construct(
        private array $items = [],
    ) {
        $this->mutationGuard = new MutationGuard();
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function data(): array
    {
        return $this->items;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected function createWithData(array $data): static
    {
        return new self(items: $data);
    }

    public static function from(iterable $items): static
    {
        return self::make(items: $items);
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
        if (array_key_exists(key: $key, array: $this->items)) {
            return true;
        }

        return new HasValue(items: $this->items)->check(key: $key);
    }

    #[NoDiscard]
    public function set(string $key, mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        if (str_contains(haystack: $key, needle: '.')) {
            return new self(
                items: new PutValueByPath(items: $this->items)->put(path: $key, value: $value),
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
            new DotPath(path: $key)->unsetValue(items: $items);

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
            items: new AppendValue(items: $this->items)->append(value: $value),
        );
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

    public function pull(string $key): Pair
    {
        $this->mutationGuard->assertMutable();

        [$value, $items] = new PullValue(items: $this->items)->pull(key: $key);

        return new Pair(
            first : $value,
            second: $items === $this->items ? $this : new self(items: $items),
        );
    }

    #[NoDiscard]
    public function map(callable $callback): static
    {
        return new self(
            items: new MapValues(items: $this->items)->map(callback: $callback),
        );
    }

    #[NoDiscard]
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

    #[NoDiscard]
    public function reject(callable $callback): static
    {
        return new self(
            items: new RejectValues(items: $this->items)->reject(callback: $callback),
        );
    }

    #[NoDiscard]
    public function flatten(int $depth = PHP_INT_MAX): static
    {
        return new self(
            items: new FlattenValues(items: $this->items)->flatten(depth: max(1, $depth)),
        );
    }

    #[NoDiscard]
    public function each(callable $callback): static
    {
        return new self(
            items: new EachValues(items: $this->items)->each(callback: $callback),
        );
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

    #[NoDiscard]
    public function sort(?callable $callback = null): static
    {
        return new self(
            items: new SortValues(items: $this->items)->sort(callback: $callback),
        );
    }

    #[NoDiscard]
    public function sortBy(string|callable $key, bool $descending = false): static
    {
        return new self(
            items: new SortValuesBy(items: $this->items)->sortBy(key: $key, options: SORT_REGULAR, descending: $descending),
        );
    }

    #[NoDiscard]
    public function reverse(): static
    {
        return new self(
            items: new ReverseValues(items: $this->items)->reverse(),
        );
    }

    #[NoDiscard]
    public function shuffle(): static
    {
        return new self(
            items: new ShuffleValues(items: $this->items)->shuffle(),
        );
    }

    #[NoDiscard]
    public function unique(): static
    {
        return new self(
            items: new UniqueValues(items: $this->items)->unique(),
        );
    }

    #[NoDiscard]
    public function chunk(int $size): static
    {
        return new self(
            items: new ChunkValues(items: $this->items)->chunk(size: $size),
        );
    }

    /**
     * @return array<array-key, list<mixed>>
     */
    public function groupBy(string|callable $key): array
    {
        return new GroupValues(items: $this->items)->group(key: $key);
    }

    /**
     * @return array{static, static}
     */
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

    public function toArray(): array
    {
        return $this->items;
    }

    public function toJson(int $flags = 0): string
    {
        return new ConvertCollectionToJson(items: $this->items)->toJson(flags: $flags);
    }

    public function toXml(string $rootElement = 'root'): string
    {
        return new ConvertCollectionToXml(items: $this->items)->toXml(rootElement: $rootElement);
    }

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
