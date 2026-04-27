<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections;

use Countable;
use IteratorAggregate;

/**
 * Contract for a typed, immutable collection of items.
 *
 * Collections are the primary data structure for working with lists of values
 * in Avax. Every mutation returns a new Collection instance.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends IteratorAggregate<TKey, TValue>
 */
interface CollectionInterface extends Countable, IteratorAggregate
{
    /**
     * @return array<TKey, TValue>
     */
    public function all(): array;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    public function has(int|string $key): bool;

    /**
     * @return TValue|null
     */
    public function get(int|string $key, mixed $default = null): mixed;

    /**
     * @return TValue|null
     */
    public function first(): mixed;

    /**
     * @return TValue|null
     */
    public function last(): mixed;

    /**
     * @return array<TKey>
     */
    public function keys(): array;

    /**
     * @return array<int, TValue>
     */
    public function values(): array;

    /**
     * @return static
     */
    public function map(callable $callback) : static;

    /**
     * @return static
     */
    public function filter(callable $callback) : static;

    public function reduce(callable $callback, mixed $initial = null) : mixed;

    /**
     * @return static
     */
    public function each(callable $callback) : static;

    /**
     * @return static
     */
    public function sortBy(callable $callback) : static;

    /**
     * @return static
     */
    public function reverse() : static;

    /**
     * @return static
     */
    public function unique() : static;

    /**
     * @return static
     */
    public function slice(int $offset, int|null $length = null) : static;

    /**
     * @return static
     */
    public function take(int $count) : static;

    /**
     * @return static
     */
    public function skip(int $count) : static;

    /**
     * @return array<mixed, static>
     */
    public function groupBy(callable $callback) : array;

    /**
     * @return array<mixed, TValue>
     */
    public function keyBy(callable $callback) : array;

    /**
     * @return array<int, mixed>
     */
    public function pluck(string $key) : array;

    /**
     * @return static
     */
    public function flatten(int $depth = INF) : static;

    /**
     * @return static
     */
    public function merge(array $items) : static;

    public function contains(mixed $value) : bool;

    /**
     * @return TValue|null
     */
    public function firstWhere(string $key, mixed $value) : mixed;

    public function sum(callable|string|null $callback = null) : int|float;

    public function avg(callable|string|null $callback = null) : int|float|null;

    public function min(callable|string|null $callback = null) : mixed;

    public function max(callable|string|null $callback = null) : mixed;

    /**
     * @return static
     */
    public function chunk(int $size) : static;

    public function toJson(int $flags = 0) : string;

    public function implode(string $glue, string|null $key = null) : string;
}