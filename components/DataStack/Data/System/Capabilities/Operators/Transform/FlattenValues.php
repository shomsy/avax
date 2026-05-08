<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

/**
 * Flattens a multi-dimensional collection into a single dimension.
 */
final readonly class FlattenValues
{
    /**
     * @param array<mixed> $items
     */
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param int<1, max> $depth Maximum nesting depth to flatten.
     *
     * @return array<mixed>
     */
    public function __invoke(int $depth = PHP_INT_MAX) : array
    {
        return $this->flatten(depth: $depth);
    }

    /**
     * @param int<1, max> $depth Maximum nesting depth to flatten.
     *
     * @return array<mixed>
     */
    public function flatten(int $depth = PHP_INT_MAX) : array
    {
        $result = [];

        foreach ($this->items as $item) {
            if (! is_array(value: $item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values(array: $item));
            } else {
                $result = array_merge($result, (new self(items: $item))->flatten(depth: $depth - 1));
            }
        }

        return $result;
    }

    /**
     * @return array<mixed>
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
