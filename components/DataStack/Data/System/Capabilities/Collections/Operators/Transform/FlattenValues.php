<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Flattens a multi-dimensional collection into a single dimension.
 */
final readonly class FlattenValues
{
    public function __construct(private array $items = []) {}

    public function __invoke(int $depth = INF) : array
    {
        $result = [];
        foreach ($this->items as $item) {
            if (! is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, (new self($item))(__invoke: $depth - 1));
            }
        }

        return $result;
    }
}
