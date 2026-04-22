<?php

declare(strict_types=1);

namespace Avax\DataHandling;

use ArrayIterator;
use Avax\DataHandling\Contracts\ArrhaeInterface;
use Avax\DataHandling\Traits\Access\AccessTrait;
use Avax\DataHandling\Traits\Aggregation\AggregationTrait;
use Avax\DataHandling\Traits\Combine\CombineTrait;
use Avax\DataHandling\Traits\Conversion\ConversionTrait;
use Avax\DataHandling\Traits\Lockable\LockableTrait;
use Avax\DataHandling\Traits\Order\OrderTrait;
use Avax\DataHandling\Traits\Search\FuzzySearchTrait;
use Avax\DataHandling\Traits\Search\SearchTrait;
use Avax\DataHandling\Traits\Transform\TransformTrait;
use ArrayAccess;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Arrhae - immutable array manipulation with modern PHP 8.5 design.
 *
 * @implements ArrhaeInterface
 */
final readonly class Arrhae implements ArrhaeInterface, IteratorAggregate, Countable
{
    use AccessTrait;
    use AggregationTrait;
    use CombineTrait;
    use ConversionTrait;
    use LockableTrait;
    use OrderTrait;
    use SearchTrait;
    use FuzzySearchTrait;
    use TransformTrait;

    public function __construct(
        private array $items = [],
    ) {}

    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        if (str_contains($key, '.')) {
            return $this->getDotNotation($key, $default);
        }

        return $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items)
            || (str_contains($key, '.') && $this->getDotNotation($key) !== null);
    }

    public function set(string $key, mixed $value): static
    {
        $this->assertNotLocked();

        if (str_contains($key, '.')) {
            $items = $this->items;
            $this->setDotNotation($items, $key, $value);

            return $this->withItems($items);
        }

        $items = $this->items;
        $items[$key] = $value;

        return $this->withItems($items);
    }

    public function forget(string $key): static
    {
        $this->assertNotLocked();

        if (! array_key_exists($key, $this->items) && str_contains($key, '.')) {
            return $this->withItems($this->unsetDotNotation($this->items, $key));
        }

        $items = $this->items;
        unset($items[$key]);

        return $this->withItems($items);
    }

    public function add(mixed $value): static
    {
        $this->assertNotLocked();

        $items = $this->items;
        $items[] = $value;

        return $this->withItems($items);
    }

    public function merge(array $items): static
    {
        return $this->withItems(array_merge($this->items, $items));
    }

    public function union(array $items): static
    {
        return $this->withItems($this->items + $items);
    }

    public function diff(array $items): static
    {
        return $this->withItems(array_diff($this->items, $items));
    }

    public function intersect(array $items): static
    {
        return $this->withItems(array_intersect($this->items, $items));
    }

    protected function getItems(): array
    {
        return $this->items;
    }

    protected function withItems(array $items): static
    {
        return new self($items);
    }

    private function getDotNotation(string $key, mixed $default = null): mixed
    {
        $array = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    private function setDotNotation(array &$items, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$items;

        while (count($keys) > 1) {
            $segment = array_shift($keys);

            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current[array_shift($keys)] = $value;
    }

    private function unsetDotNotation(array $items, string $key): array
    {
        $keys = explode('.', $key);
        $current = &$items;

        while (count($keys) > 1) {
            $segment = array_shift($keys);

            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                return $items;
            }

            $current = &$current[$segment];
        }

        unset($current[array_shift($keys)]);

        return $items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function isNotEmpty(): bool
    {
        return $this->items !== [];
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertNotLocked();
        $this->withItems($this->items); // Ensure new instance
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->assertNotLocked();
        $this->withItems($this->items);
    }

    public static function make(iterable $items = []): static
    {
        if (is_array($items)) {
            return new self($items);
        }

        return new self(iterator_to_array($items));
    }

    public static function from(iterable $items): static
    {
        return self::make($items);
    }

    public static function wrap(mixed $value): static
    {
        return match (true) {
            $value instanceof static => $value,
            is_array($value) => new self($value),
            default => new self([$value]),
        };
    }
}