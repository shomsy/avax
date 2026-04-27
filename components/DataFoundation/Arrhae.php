<?php

declare(strict_types=1);

namespace components\DataFoundation;

use ArrayIterator;
use components\DataFoundation\Collections\Create\MakeCollection;
use components\DataFoundation\Collections\Create\WrapValue;
use components\DataFoundation\Composites\Pair\Pair;
use components\DataFoundation\Contracts\ArrhaeInterface;
use components\DataFoundation\Internal\Mutability\MutationGuard;
use Exception;
use InvalidArgumentException;
use LogicException;
use SimpleXMLElement;
use Traversable;

/**
 * Arrhae - raw array facade for simple array manipulation.
 */
final readonly class Arrhae implements ArrhaeInterface
{
    private MutationGuard $guard;

    public function __construct(
        private array $items = [],
    )
    {
        $this->guard = new MutationGuard();
    }

    public static function from(iterable $items) : static
    {
        return self::make(items: $items);
    }

    public static function make(iterable $items = []) : static
    {
        return new self(items: new MakeCollection()->from(items: $items));
    }

    public static function wrap(mixed $value) : static
    {
        return match (true) {
            $value instanceof static => $value,
            default                  => new self(items: new WrapValue()->intoArray(value: $value)),
        };
    }

    public function all() : array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return $this->items[$key];
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return $this->getDotNotation(key: $key, default: $default);
        }

        return $default;
    }

    private function getDotNotation(string $key, mixed $default = null) : mixed
    {
        $array = $this->items;

        foreach (explode(separator: '.', string: $key) as $segment) {
            if (! is_array(value: $array) || ! array_key_exists(key: $segment, array: $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public function has(string $key) : bool
    {
        return array_key_exists(key: $key, array: $this->items)
            || (str_contains(haystack: $key, needle: '.') && $this->getDotNotation(key: $key) !== null);
    }

    public function set(string $key, mixed $value) : static
    {
        $this->assertNotLocked();

        if (str_contains(haystack: $key, needle: '.')) {
            $items = $this->items;
            $this->setDotNotation(items: $items, key: $key, value: $value);

            return new self(items: $items);
        }

        $items       = $this->items;
        $items[$key] = $value;

        return new self(items: $items);
    }

    private function assertNotLocked() : void
    {
        $this->guard->assertMutable();
    }

    private function setDotNotation(array &$items, string $key, mixed $value) : void
    {
        $keys    = explode(separator: '.', string: $key);
        $current = &$items;

        while ( count(value: $keys) > 1 ) {
            $segment = array_shift(array: $keys);

            if (! isset($current[$segment]) || ! is_array(value: $current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current[array_shift(array: $keys)] = $value;
    }

    public function forget(string $key) : static
    {
        $this->assertNotLocked();

        if (! array_key_exists(key: $key, array: $this->items) && str_contains(haystack: $key, needle: '.')) {
            $items = $this->items;
            $this->unsetDotNotation(items: $items, key: $key);

            return new self(items: $items);
        }

        $items = $this->items;
        unset($items[$key]);

        return new self(items: $items);
    }

    private function unsetDotNotation(array &$items, string $key) : void
    {
        $keys    = explode(separator: '.', string: $key);
        $current = &$items;

        while ( count(value: $keys) > 1 ) {
            $segment = array_shift(array: $keys);

            if (! isset($current[$segment]) || ! is_array(value: $current[$segment])) {
                return;
            }

            $current = &$current[$segment];
        }

        unset($current[array_shift(array: $keys)]);
    }

    public function add(mixed $value) : static
    {
        $this->assertNotLocked();

        $items   = $this->items;
        $items[] = $value;

        return new self(items: $items);
    }

    public function pull(string $key) : Pair
    {
        $this->assertNotLocked();

        $value = $this->items[$key] ?? null;
        $items = $this->items;

        if (array_key_exists(key: $key, array: $this->items)) {
            unset($items[$key]);
        }

        return new Pair(
            first : $value,
            second: $items === $this->items ? $this : new self(items: $items),
        );
    }

    public function merge(array $items) : static
    {
        return new self(items: array_merge($this->items, $items));
    }

    public function union(array $items) : static
    {
        return new self(items: $this->items + $items);
    }

    public function diff(array $items) : static
    {
        return new self(items: array_diff($this->items, $items));
    }

    public function intersect(array $items) : static
    {
        return new self(items: array_intersect($this->items, $items));
    }

    public function count() : int
    {
        return count(value: $this->items);
    }

    public function isEmpty() : bool
    {
        return $this->items === [];
    }

    public function isNotEmpty() : bool
    {
        return $this->items !== [];
    }

    public function first(mixed $default = null) : mixed
    {
        if ($this->items === []) {
            return $default;
        }

        return reset(array: $this->items);
    }

    public function last(mixed $default = null) : mixed
    {
        if ($this->items === []) {
            return $default;
        }

        return end(array: $this->items);
    }

    public function map(callable $callback) : static
    {
        return new self(items: array_map(callback: $callback, array: $this->items));
    }

    public function filter(callable $callback) : static
    {
        return new self(items: array_filter(array: $this->items, callback: $callback, mode: ARRAY_FILTER_USE_BOTH));
    }

    public function reduce(callable $callback, mixed $initial = null) : mixed
    {
        return array_reduce(array: $this->items, callback: $callback, initial: $initial);
    }

    public function average(string|callable $key) : float
    {
        $count = count(value: $this->items);

        return $count !== 0 ? $this->sum(key: $key) / $count : 0.0;
    }

    public function sum(string|callable $key) : int|float
    {
        if ($this->items === []) {
            return 0;
        }

        return array_reduce(
            array   : $this->items,
            callback: fn (int|float $carry, mixed $item) : int|float => $carry + $this->extractValue(item: $item, key: $key),
            initial : 0
        );
    }

    private function extractValue(mixed $item, string|callable $key) : int|float
    {
        $value = is_callable(value: $key) ? $key($item) : ($item[$key] ?? 0);

        if (! is_numeric(value: $value)) {
            throw new InvalidArgumentException(message: 'Non-numeric value encountered in aggregation.');
        }

        return $value;
    }

    public function min(string|callable $key) : mixed
    {
        if ($this->items === []) {
            throw new LogicException(message: 'Cannot find minimum of empty collection.');
        }

        $values = array_map(
            callback: static fn (mixed $item) : mixed => is_callable(value: $key) ? $key($item) : ($item[$key] ?? null),
            array   : $this->items
        );

        return min(value: $values);
    }

    public function max(string|callable $key) : mixed
    {
        if ($this->items === []) {
            throw new LogicException(message: 'Cannot find maximum of empty collection.');
        }

        $values = array_map(
            callback: static fn (mixed $item) : mixed => is_callable(value: $key) ? $key($item) : ($item[$key] ?? null),
            array   : $this->items
        );

        return max(value: $values);
    }

    public function contains(mixed $value) : bool
    {
        return in_array(needle: $value, haystack: $this->items, strict: true);
    }

    public function search(mixed $value) : int|false
    {
        return array_search(needle: $value, haystack: $this->items, strict: true);
    }

    public function whereIn(string $key, array $values) : static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item) : bool => in_array(needle: $item[$key] ?? null, haystack: $values, strict: true)
        );

        return new self(items: $filtered);
    }

    public function whereBetween(string $key, array $range) : static
    {
        [$min, $max] = $range;

        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item) : bool => ($item[$key] ?? null) >= $min && ($item[$key] ?? null) <= $max
        );

        return new self(items: $filtered);
    }

    public function whereNull(string $key) : static
    {
        return $this->where(key: $key, value: null);
    }

    public function where(string $key, mixed $value) : static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item) : bool => ($item[$key] ?? null) === $value
        );

        return new self(items: $filtered);
    }

    public function whereNotNull(string $key) : static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $item) : bool => ($item[$key] ?? null) !== null
        );

        return new self(items: $filtered);
    }

    public function sort(callable|null $callback = null) : static
    {
        $items = $this->items;

        if ($callback !== null) {
            uasort(array: $items, callback: $callback);
        } else {
            sort(array: $items);
        }

        return new self(items: $items);
    }

    public function reverse() : static
    {
        return new self(items: array_reverse(array: $this->items, preserve_keys: true));
    }

    public function shuffle() : static
    {
        $items = $this->items;
        shuffle(array: $items);

        return new self(items: $items);
    }

    public function unique() : static
    {
        return new self(items: array_unique(array: $this->items, flags: SORT_REGULAR));
    }

    public function chunk(int $size) : static
    {
        return new self(items: array_chunk(array: $this->items, length: $size, preserve_keys: true));
    }

    public function aggregateGroupBy(string|callable $key) : array
    {
        return $this->groupBy(key: $key);
    }

    public function groupBy(string|callable $key) : array
    {
        $grouped = [];

        foreach ($this->items as $item) {
            $groupKey             = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $grouped[$groupKey][] = $item;
        }

        return $grouped;
    }

    public function countBy(string|callable $key) : array
    {
        $counts = [];

        foreach ($this->items as $item) {
            $groupKey          = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $counts[$groupKey] = ($counts[$groupKey] ?? 0) + 1;
        }

        return $counts;
    }

    public function keyBy(string|callable $key) : static
    {
        $keyed = [];

        foreach ($this->items as $item) {
            $itemKey         = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $keyed[$itemKey] = $item;
        }

        return new self(items: $keyed);
    }

    public function pluck(string|callable $key) : array
    {
        return array_map(
            callback: static fn (mixed $item) : mixed => is_callable(value: $key) ? $key($item) : ($item[$key] ?? null),
            array   : $this->items
        );
    }

    public function keys() : array
    {
        return array_keys(array: $this->items);
    }

    public function values() : static
    {
        return new self(items: array_values(array: $this->items));
    }

    public function flip() : static
    {
        return new self(items: array_flip(array: $this->items));
    }

    public function toJson(int $flags = 0) : string
    {
        $json = json_encode(value: $this->items, flags: $flags);

        if ($json === false) {
            throw new InvalidArgumentException(
                message: 'Failed to encode collection to JSON: ' . json_last_error_msg()
            );
        }

        return $json;
    }

    public function toArray() : array
    {
        return array_map(
            callback: fn (mixed $item) : mixed => $this->normalizeItem(item: $item),
            array   : $this->items
        );
    }

    private function normalizeItem(mixed $item) : mixed
    {
        if (is_object(value: $item) && method_exists(object_or_class: $item, method: 'toArray')) {
            return $item->toArray();
        }

        return $item;
    }

    public function toXml(string $rootElement = 'root') : string
    {
        try {
            $xml = new SimpleXMLElement(data: "<{$rootElement}/>");
            $this->arrayToXml(data: $this->items, xml: $xml);

            return $xml->asXML() ?: '';
        } catch (Exception $exception) {
            throw new LogicException(
                message : 'Failed to convert collection to XML: ' . $exception->getMessage(),
                code    : $exception->getCode(),
                previous: $exception
            );
        }
    }

    private function arrayToXml(array $data, SimpleXMLElement $xml) : void
    {
        foreach ($data as $key => $value) {
            $tagName = is_numeric(value: $key) ? 'item' : $key;

            if (is_array(value: $value)) {
                $child = $xml->addChild(qualifiedName: $tagName);
                $this->arrayToXml(data: $value, xml: $child);
            } else {
                $xml->addChild(qualifiedName: $tagName, value: htmlspecialchars(string: (string) $value));
            }
        }
    }

    public function only(array $keys) : static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $_, mixed $key) : bool => in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH
        );

        return new self(items: $filtered);
    }

    public function except(array $keys) : static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $_, mixed $key) : bool => ! in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH
        );

        return new self(items: $filtered);
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

    public function isLocked() : bool
    {
        return $this->guard->isLocked();
    }

    public function toImmutable() : static
    {
        return $this->lock();
    }

    public function lock() : static
    {
        $clone = new self(items: $this->items);
        $clone->guard->lock();

        return $clone;
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->items);
    }

    public function offsetExists(mixed $offset) : bool
    {
        return array_key_exists(key: (string) $offset, array: $this->items);
    }

    public function offsetGet(mixed $offset) : mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $this->assertNotLocked();
    }

    public function offsetUnset(mixed $offset) : void
    {
        $this->assertNotLocked();
    }

    public function collect() : Collection
    {
        return new Collection(items: $this->items);
    }
}
