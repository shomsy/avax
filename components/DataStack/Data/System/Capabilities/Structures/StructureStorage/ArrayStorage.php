<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Countable;
use Override;

final readonly class ArrayStorage implements Countable, StructureStorage
{
    /** @var list<mixed> */
    private array $items;

    /**
     * @param iterable<mixed> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->items = array_values(is_array(value: $items) ? $items : iterator_to_array(iterator: $items));
    }

    /**
     * @return list<mixed>
     */
    public function values() : array
    {
        return $this->items;
    }

    public function append(mixed $value) : self
    {
        return new self(items: [...$this->items, $value]);
    }

    public function read(int $index) : mixed
    {
        if (! array_key_exists(key: $index, array: $this->items)) {
            throw IndexOutOfBounds::at(index: $index);
        }

        return $this->items[$index];
    }

    public function replace(int $index, mixed $value) : self
    {
        if (! array_key_exists(key: $index, array: $this->items)) {
            throw IndexOutOfBounds::at(index: $index);
        }

        $items         = $this->items;
        $items[$index] = $value;

        return new self(items: $items);
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->items);
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->items === [];
    }
}
