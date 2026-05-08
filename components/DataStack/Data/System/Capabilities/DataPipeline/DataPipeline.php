<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataPipeline;

use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\Pair;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\FlipValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PullValue;

/**
 * DataPipeline — shared pipeline behavior for Arrhae, Collection, and Json.
 *
 * Provides identical query, transform, pipeline, set-algebra, and selection methods
 * so all three data DSLs share the same fluent vocabulary:
 *   sum, map, filter, where, pluck, groupBy, sortBy, unique, chunk,
 *   only, except, merge, union, diff, intersect, flip, keys, values,
 *   tap, when, unless, keyBy, pull, values.
 *
 * Requires the consuming class to implement two hooks:
 *   - data(): array         — returns the underlying array
 *   - createWithData(array): static — returns a new instance with given data
 *
 * This trait is an implementation technique, not an architectural identity.
 * Composition over inheritance. The trait supplies behavior; the class owns state.
 */
trait DataPipeline
{
    // -- Selection ----------------------------------------------------------------

    /**
     * @param list<array-key> $keys
     */
    public function only(array $keys) : static
    {
        $filtered = array_filter(
            array   : $this->data(),
            callback: static fn (mixed $_, mixed $key) : bool => in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return $this->createWithData(data: $filtered);
    }

    /**
     * @return array<array-key, mixed>
     */
    abstract protected function data() : array;

    /**
     * @param array<array-key, mixed> $data
     */
    abstract protected function createWithData(array $data) : static;

    /**
     * @param list<array-key> $keys
     */
    public function except(array $keys) : static
    {
        $filtered = array_filter(
            array   : $this->data(),
            callback: static fn (mixed $_, mixed $key) : bool => ! in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return $this->createWithData(data: $filtered);
    }

    // -- Query / Filtering --------------------------------------------------------

    /**
     * @return list<mixed>
     */
    public function pluck(string|callable $key) : array
    {
        $plucked = [];

        foreach ($this->data() as $item) {
            $plucked[] = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
        }

        return $plucked;
    }

    public function pull(string $key) : Pair
    {
        [$value, $remaining] = new PullValue(items: $this->data())->pull(key: $key);

        return new Pair(
            first : $value,
            second: $remaining === $this->data() ? $this : $this->createWithData(data: $remaining),
        );
    }

    /**
     * @param list<mixed> $values
     */
    public function whereIn(string $key, array $values) : static
    {
        $filtered = array_filter(
            array   : $this->data(),
            callback: static fn (mixed $item) : bool => in_array(needle: $item[$key] ?? null, haystack: $values, strict: true),
        );

        return $this->createWithData(data: $filtered);
    }

    /**
     * @param array{mixed, mixed} $range
     */
    public function whereBetween(string $key, array $range) : static
    {
        [$min, $max] = $range;

        $filtered = array_filter(
            array   : $this->data(),
            callback: static fn (mixed $item) : bool => ($item[$key] ?? null) >= $min && ($item[$key] ?? null) <= $max,
        );

        return $this->createWithData(data: $filtered);
    }

    public function whereNull(string $key) : static
    {
        return $this->where(key: $key, value: null);
    }

    // -- Keying -------------------------------------------------------------------

    public function where(string $key, mixed $value) : static
    {
        $filtered = array_filter(
            array   : $this->data(),
            callback: static fn (mixed $item) : bool => ($item[$key] ?? null) === $value,
        );

        return $this->createWithData(data: $filtered);
    }

    // -- Set Algebra --------------------------------------------------------------

    public function whereNotNull(string $key) : static
    {
        $filtered = array_filter(
            array   : $this->data(),
            callback: static fn (mixed $item) : bool => ($item[$key] ?? null) !== null,
        );

        return $this->createWithData(data: $filtered);
    }

    public function keyBy(string|callable $key) : static
    {
        $keyed = [];

        foreach ($this->data() as $item) {
            $itemKey         = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $keyed[$itemKey] = $item;
        }

        return $this->createWithData(data: $keyed);
    }

    public function flip() : static
    {
        return $this->createWithData(data: new FlipValues(items: $this->data())->flip());
    }

    /**
     * @param array<array-key, mixed> $items
     */
    public function merge(array $items) : static
    {
        return $this->createWithData(data: array_merge($this->data(), $items));
    }

    /**
     * @param array<array-key, mixed> $items
     */
    public function union(array $items) : static
    {
        return $this->createWithData(data: $this->data() + $items);
    }

    // -- Info ---------------------------------------------------------------------

    /**
     * @param array<array-key, mixed> $items
     */
    public function diff(array $items) : static
    {
        return $this->createWithData(data: array_diff($this->data(), $items));
    }

    /**
     * @param array<array-key, mixed> $items
     */
    public function intersect(array $items) : static
    {
        return $this->createWithData(data: array_intersect($this->data(), $items));
    }

    // -- Conditional / Pipeline Control -------------------------------------------

    /**
     * @return list<array-key>
     */
    public function keys() : array
    {
        return array_keys(array: $this->data());
    }

    public function values() : static
    {
        return $this->createWithData(data: array_values(array: $this->data()));
    }

    public function tap(callable $callback) : static
    {
        $callback($this);

        return $this;
    }

    public function unless(bool $condition, callable $callback) : static
    {
        return $this->when(condition: ! $condition, callback: $callback);
    }

    public function when(bool $condition, callable $callback) : static
    {
        if ($condition) {
            return $callback($this) ?? $this;
        }

        return $this;
    }
}
