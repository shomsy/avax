<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\DataList;
use Avax\Components\DataStack\Data\System\Foundation\Normalization\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * @implements IteratorAggregate<int, mixed>
 */
final readonly class Sequence implements Countable, IteratorAggregate
{
    /**
     * @var array<int, mixed>
     */
    private array $items;

    /**
     * @param  iterable<mixed>  $items
     */
    public function __construct(
        iterable $items = [],
    ) {
        $this->items = NormalizedIterable::toArrayPreserveKeys(iterable: $items);
    }

    /**
     * @return array<int, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function map(callable $callback): self
    {
        return new self(items: array_values(array_map($callback, $this->items)));
    }

    public function filter(callable $callback): self
    {
        return new self(items: array_values(array_filter($this->items, $callback)));
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function toDataList(): DataList
    {
        return new DataList(items: $this->items);
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
