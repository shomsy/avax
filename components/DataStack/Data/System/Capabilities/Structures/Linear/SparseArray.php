<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\AssociativeStructure;
use Countable;
use Override;

final readonly class SparseArray implements Countable, AssociativeStructure
{
    /** @var array<int, mixed> */
    private array $items;

    /**
     * @param array<int, mixed> $items
     */
    public function __construct(array $items = [])
    {
        $normalized = [];

        foreach ($items as $index => $value) {
            if ($value !== null) {
                $normalized[(int) $index] = $value;
            }
        }

        ksort(array: $normalized);
        $this->items = $normalized;
    }

    public function put(int $index, mixed $value) : self
    {
        $items = $this->items;

        if ($value === null) {
            unset($items[$index]);
        } else {
            $items[$index] = $value;
        }

        return new self(items: $items);
    }

    #[Override]
    public function get(int|string $key, mixed $default = null) : mixed
    {
        return $this->items[(int) $key] ?? $default;
    }

    #[Override]
    public function has(int|string $key) : bool
    {
        return array_key_exists(key: (int) $key, array: $this->items);
    }

    /**
     * @return list<int>
     */
    #[Override]
    public function keys() : array
    {
        return array_keys(array: $this->items);
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray() : array
    {
        return $this->items;
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->items === [];
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->items);
    }
}
