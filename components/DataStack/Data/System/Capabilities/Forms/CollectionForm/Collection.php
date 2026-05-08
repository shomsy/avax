<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Forms\ArrayForm\Arrhae;
use Avax\Components\DataStack\Data\System\Foundation\Failure\MutationException;
use Avax\Components\DataStack\Data\System\Foundation\Mutability\MutationGuard;
use Avax\Components\DataStack\Data\System\Foundation\Normalization\MakeCollection;
use Avax\Components\DataStack\Data\System\Foundation\Normalization\WrapValue;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Functional\Pair;
use Traversable;

/**
 * Collection — fluent item/object pipeline DSL.
 *
 * Composes Arrhae internally for all array-backed pipeline operations.
 * Collection adds iterable/object semantics, IteratorAggregate, Countable,
 * and read-only ArrayAccess behavior.
 *
 * Same fluent pipeline vocabulary as Arrhae, different input semantics.
 */
final readonly class Collection implements CollectionInterface
{
    private MutationGuard $mutationGuard;

    public function __construct(
        private Arrhae $arrhae,
    ) {
        $this->mutationGuard = new MutationGuard();
    }

    // -- Factories ---------------------------------------------------------------

    /** @param iterable<array-key, mixed> $items */
    public static function make(iterable $items = []): static
    {
        return new self(arrhae: Arrhae::make(items: $items));
    }

    public static function wrap(mixed $value): static
    {
        return match (true) {
            $value instanceof static => $value,
            $value instanceof Arrhae => new self(arrhae: $value),
            default => new self(arrhae: Arrhae::wrap(value: $value)),
        };
    }

    // -- Access ------------------------------------------------------------------

    /** @return array<array-key, mixed> */
    public function all(): array
    {
        return $this->arrhae->all();
    }

        public function count(): int
    {
        $count = $this->arrhae->count();
        assert($count >= 0);

        return $count;
    }

    public function isEmpty(): bool
    {
        return $this->arrhae->isEmpty();
    }

    public function isNotEmpty(): bool
    {
        return $this->arrhae->isNotEmpty();
    }

    public function first(mixed $default = null): mixed
    {
        return $this->arrhae->first(default: $default);
    }

    public function last(mixed $default = null): mixed
    {
        return $this->arrhae->last(default: $default);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->arrhae->get(key: $key, default: $default);
    }

    public function has(string $key): bool
    {
        return $this->arrhae->has(key: $key);
    }

    public function set(string $key, mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        return new self(arrhae: $this->arrhae->set(key: $key, value: $value));
    }

    public function forget(string $key): static
    {
        $this->mutationGuard->assertMutable();

        return new self(arrhae: $this->arrhae->forget(key: $key));
    }

    public function add(mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        return new self(arrhae: $this->arrhae->add(value: $value));
    }

    public function pull(string $key): Pair
    {
        $pair = $this->arrhae->pull(key: $key);

        return new Pair(
            first : $pair->first(),
            second: $pair->second() instanceof Arrhae
                ? new self(arrhae: $pair->second())
                : $pair->second(),
        );
    }

    // -- Pipeline ----------------------------------------------------------------

    public function map(callable $callback): static
    {
        return new self(arrhae: $this->arrhae->map(callback: $callback));
    }

    public function filter(callable $callback): static
    {
        return new self(arrhae: $this->arrhae->filter(callback: $callback));
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return $this->arrhae->reduce(callback: $callback, initial: $initial);
    }

    public function reject(callable $callback): static
    {
        return new self(arrhae: $this->arrhae->reject(callback: $callback));
    }

    public function flatten(int $depth = PHP_INT_MAX): static
    {
        return new self(arrhae: $this->arrhae->flatten(depth: $depth));
    }

    public function each(callable $callback): static
    {
        return new self(arrhae: $this->arrhae->each(callback: $callback));
    }

    // -- Aggregate ---------------------------------------------------------------

    public function sum(string|callable $key): int|float
    {
        return $this->arrhae->sum(key: $key);
    }

    public function average(string|callable $key): float
    {
        return $this->arrhae->average(key: $key);
    }

    public function min(string|callable $key): mixed
    {
        return $this->arrhae->min(key: $key);
    }

    public function max(string|callable $key): mixed
    {
        return $this->arrhae->max(key: $key);
    }

    // -- Order -------------------------------------------------------------------

    public function sort(?callable $callback = null): static
    {
        return new self(arrhae: $this->arrhae->sort(callback: $callback));
    }

    public function sortBy(string|callable $key, bool $descending = false): static
    {
        return new self(arrhae: $this->arrhae->sortBy(key: $key, descending: $descending));
    }

    public function reverse(): static
    {
        return new self(arrhae: $this->arrhae->reverse());
    }

    public function shuffle(): static
    {
        return new self(arrhae: $this->arrhae->shuffle());
    }

    public function unique(): static
    {
        return new self(arrhae: $this->arrhae->unique());
    }

    // -- Grouping ----------------------------------------------------------------

    public function chunk(int $size): static
    {
        return new self(arrhae: $this->arrhae->chunk(size: $size));
    }

    /** @return array<array-key, list<mixed>> */
    public function groupBy(string|callable $key): array
    {
        return $this->arrhae->groupBy(key: $key);
    }

    /** @return array{static, static} */
    public function partition(callable $callback): array
    {
        [$passArrhae, $failArrhae] = $this->arrhae->partition(callback: $callback);

        return [new self(arrhae: $passArrhae), new self(arrhae: $failArrhae)];
    }

    // -- Search ------------------------------------------------------------------

    public function contains(mixed $value): bool
    {
        return $this->arrhae->contains(value: $value);
    }

    public function search(mixed $value): int|false
    {
        return $this->arrhae->search(value: $value);
    }

    // -- Selection ---------------------------------------------------------------

    /** @param list<int|string> $keys */
    public function only(array $keys): static
    {
        return new self(arrhae: $this->arrhae->only(keys: $keys));
    }

    /** @param list<int|string> $keys */
    public function except(array $keys): static
    {
        return new self(arrhae: $this->arrhae->except(keys: $keys));
    }

    /** @return list<mixed> */
    public function pluck(string|callable $key): array
    {
        return $this->arrhae->pluck(key: $key);
    }

    // -- Query / Filtering -------------------------------------------------------

    public function where(string $key, mixed $value): static
    {
        return new self(arrhae: $this->arrhae->where(key: $key, value: $value));
    }

    /** @param list<mixed> $values */
    public function whereIn(string $key, array $values): static
    {
        return new self(arrhae: $this->arrhae->whereIn(key: $key, values: $values));
    }

    /** @param array{mixed, mixed} $range */
    public function whereBetween(string $key, array $range): static
    {
        return new self(arrhae: $this->arrhae->whereBetween(key: $key, range: $range));
    }

    public function whereNull(string $key): static
    {
        return new self(arrhae: $this->arrhae->whereNull(key: $key));
    }

    public function whereNotNull(string $key): static
    {
        return new self(arrhae: $this->arrhae->whereNotNull(key: $key));
    }

    // -- Keying ------------------------------------------------------------------

    public function keyBy(string|callable $key): static
    {
        return new self(arrhae: $this->arrhae->keyBy(key: $key));
    }

    // -- Set Algebra -------------------------------------------------------------

    public function flip(): static
    {
        return new self(arrhae: $this->arrhae->flip());
    }

    /** @param array<array-key, mixed> $items */
    
    /** @param array<array-key, mixed> $items */
    public function merge(array $items): static
    {
        return new self(arrhae: $this->arrhae->merge(items: $items));
    }

    /** @param array<array-key, mixed> $items */
    
    /** @param array<array-key, mixed> $items */
    public function union(array $items): static
    {
        return new self(arrhae: $this->arrhae->union(items: $items));
    }

    /** @param array<array-key, mixed> $items */
    
    /** @param array<array-key, mixed> $items */
    public function diff(array $items): static
    {
        return new self(arrhae: $this->arrhae->diff(items: $items));
    }

    /** @param array<array-key, mixed> $items */
    
    /** @param array<array-key, mixed> $items */
    public function intersect(array $items): static
    {
        return new self(arrhae: $this->arrhae->intersect(items: $items));
    }

    // -- Info --------------------------------------------------------------------

    /** @return list<array-key> */
    public function keys(): array
    {
        return $this->arrhae->keys();
    }

    public function values(): static
    {
        return new self(arrhae: $this->arrhae->values());
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
        return $this->arrhae->toArray();
    }

    public function toJson(int $flags = 0): string
    {
        return $this->arrhae->toJson(flags: $flags);
    }

    public function toXml(string $rootElement = 'root'): string
    {
        return $this->arrhae->toXml(rootElement: $rootElement);
    }

    // -- Immutability ------------------------------------------------------------

    public function toImmutable(): static
    {
        return $this->lock();
    }

    public function lock(): static
    {
        if ($this->mutationGuard->isLocked()) {
            throw MutationException::collectionIsAlreadyLocked();
        }

        $clone = new self(arrhae: $this->arrhae);
        $clone->mutationGuard->lock();

        return $clone;
    }

    public function isLocked(): bool
    {
        return $this->mutationGuard->isLocked();
    }

    // -- IteratorAggregate -------------------------------------------------------

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array: $this->arrhae->all());
    }

    // -- ArrayAccess (read-only) -------------------------------------------------

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists(key: (string) $offset, array: $this->arrhae->all());
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->arrhae->all()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->mutationGuard->assertMutable();

        throw MutationException::arrayStyleMutationNotSupported(
            hint: 'Use set(), forget(), or add() instead.',
        );
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->mutationGuard->assertMutable();

        throw MutationException::arrayStyleMutationNotSupported(
            hint: 'Use forget() instead.',
        );
    }
}
