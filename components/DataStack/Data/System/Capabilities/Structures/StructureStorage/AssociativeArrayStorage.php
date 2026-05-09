<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Countable;
use Override;

final readonly class AssociativeArrayStorage implements Countable, StructureStorage
{
    /** @var array<array-key, mixed> */
    private array $items;

    /**
     * @param iterable<array-key, mixed> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->items = is_array(value: $items) ? $items : iterator_to_array(iterator: $items);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function values() : array
    {
        return $this->items;
    }

    public function has(int|string $key) : bool
    {
        return array_key_exists(key: $key, array: $this->items);
    }

    public function read(int|string $key, mixed $default = null) : mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function write(int|string $key, mixed $value) : self
    {
        $items       = $this->items;
        $items[$key] = $value;

        return new self(items: $items);
    }

    public function remove(int|string $key) : self
    {
        $items = $this->items;
        unset($items[$key]);

        return new self(items: $items);
    }

    /**
     * @return list<array-key>
     */
    public function keys() : array
    {
        return array_keys(array: $this->items);
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
