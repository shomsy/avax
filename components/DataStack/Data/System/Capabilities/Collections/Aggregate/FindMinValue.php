<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Aggregate;

use LogicException;

/**
 * Finds minimum value by key.
 */
final readonly class FindMinValue
{
    public function __construct(
        private array $items = [],
    ) {}


    public function __invoke(string|callable $key) : mixed
    {
        return $this->min(key: $key);
    }


    public function min(string|callable $key) : mixed
    {
        if ($this->items === []) {
            throw new LogicException(message: 'Cannot find minimum of empty collection.');
        }

        $values = array_map(
            callback: static fn (mixed $item) : mixed => is_callable(value: $key) ? $key($item) : ($item[$key] ?? null),
            array   : $this->items,
        );

        return min(value: $values);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
