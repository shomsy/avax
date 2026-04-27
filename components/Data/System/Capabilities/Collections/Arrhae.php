<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections;

use Avax\Components\Data\System\Capabilities\Collections\Create\MakeCollection;
use Avax\Components\Data\System\Capabilities\Collections\Create\WrapValue;
use Avax\Components\Data\System\Capabilities\Collections\Internal\Mutability\MutationGuard;

/**
 * Arrhae - raw array facade for simple array manipulation.
 * Recovered from legacy DataFoundation.
 */
final readonly class Arrhae
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

    public function merge(array $items) : static
    {
        return new self(items: array_merge($this->items, $items));
    }

    public function count() : int
    {
        return count(value: $this->items);
    }

    public function isEmpty() : bool
    {
        return $this->items === [];
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

    public function toArray() : array
    {
        return $this->items;
    }

    public function isLocked() : bool
    {
        return $this->guard->isLocked();
    }

    public function lock() : static
    {
        $clone = new self(items: $this->items);
        $clone->guard->lock();

        return $clone;
    }
}
