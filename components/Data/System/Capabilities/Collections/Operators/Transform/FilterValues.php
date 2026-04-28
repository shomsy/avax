<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Filters collection items by a callback.
 */
final readonly class FilterValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(callable $callback) : array
    {
        return array_filter(array: $this->items, callback: $callback, mode: ARRAY_FILTER_USE_BOTH);
    }
}
