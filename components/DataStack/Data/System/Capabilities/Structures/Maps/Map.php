<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Maps;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Maps\MapEntry;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\DataList;
use Avax\Components\DataStack\Data\System\Foundation\Normalization\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * @implements IteratorAggregate<array-key, mixed>
 */
final readonly class Map implements Countable, IteratorAggregate
{
    /**
     * @var array<array-key, mixed>
     */
    private array $items;

    /**
     * @param  iterable<array-key, mixed>  $items
     */
    public function __construct(
        iterable $items = [],
    ) {
        $this->items = NormalizedIterable::toArrayPreserveKeys(iterable: $items);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function get(int|string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function put(int|string $key, mixed $value): self
    {
        $items = $this->items;
        $items[$key] = $value;

        return new self(items: $items);
    }

    public function remove(int|string $key): self
    {
        $items = $this->items;
        unset($items[$key]);

        return new self(items: $items);
    }

    /**
     * @return array<int, array-key>
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    public function values(): DataList
    {
        return new DataList(items: array_values($this->items));
    }

    /**
     * @return array<int, MapEntry>
     */
    public function entries(): array
    {
        $entries = [];

        foreach ($this->items as $key => $value) {
            $entries[] = new MapEntry(key: $key, value: $value);
        }

        return $entries;
    }

    #[Override]
    public function count(): int
    {
        return count($this->items);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator(array: $this->items);
    }
}
