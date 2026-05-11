<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm;

use ArrayAccess;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Functional\Pair;
use Countable;
use IteratorAggregate;

/**
 * Collection contract — defines the public surface for fluent collection API.
 *
 * @extends ArrayAccess<array-key, mixed>
 * @extends IteratorAggregate<array-key, mixed>
 */
interface CollectionInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /** @return array<array-key, mixed> */
    public function all(): array;

    public function count(): int;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    public function first(mixed $default = null): mixed;

    public function last(mixed $default = null): mixed;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function set(string $key, mixed $value): static;

    public function forget(string $key): static;

    public function add(mixed $value): static;

    public function pull(string $key): Pair;

    public function map(callable $callback): static;

    public function filter(callable $callback): static;

    public function reduce(callable $callback, mixed $initial = null): mixed;

    public function sum(string|callable $key): int|float;

    public function average(string|callable $key): float;

    public function min(string|callable $key): mixed;

    public function max(string|callable $key): mixed;

    public function chunk(int $size): static;

    /** @return array<array-key, list<mixed>> */
    public function groupBy(string|callable $key): array;

    public function keyBy(string|callable $key): static;

    /** @return array{static, static} */
    public function partition(callable $callback): array;

    public function contains(mixed $value): bool;

    public function search(mixed $value): int|false;

    public function where(string $key, mixed $value): static;

    /** @param list<mixed> $values */
    public function whereIn(string $key, array $values): static;

    /** @param array{mixed, mixed} $range */
    public function whereBetween(string $key, array $range): static;

    public function whereNull(string $key): static;

    public function whereNotNull(string $key): static;

    public function sort(callable|null $callback = null) : static;

    public function sortBy(string|callable $key, bool $descending = false): static;

    public function reverse(): static;

    public function shuffle(): static;

    public function unique(): static;

    /** @return array<array-key, mixed> */
    public function toArray(): array;

    public function toJson(int $flags = 0): string;

    public function toXml(string $rootElement = 'root'): string;

    /** @param list<int|string> $keys */
    public function only(array $keys): static;

    /** @param list<int|string> $keys */
    public function except(array $keys): static;

    /** @return list<mixed> */
    public function pluck(string|callable $key): array;

    /** @return list<array-key> */
    public function keys(): array;

    public function values(): static;

    public function flip(): static;

    /** @param array<array-key, mixed> $items */
    public function merge(array $items): static;

    /** @param array<array-key, mixed> $items */
    public function union(array $items): static;

    /** @param array<array-key, mixed> $items */
    public function diff(array $items): static;

    /** @param array<array-key, mixed> $items */
    public function intersect(array $items): static;

    public function tap(callable $callback): static;

    public function when(bool $condition, callable $callback): static;

    public function unless(bool $condition, callable $callback): static;

    public function isLocked(): bool;

    public function lock(): static;

    public function toImmutable(): static;
}
