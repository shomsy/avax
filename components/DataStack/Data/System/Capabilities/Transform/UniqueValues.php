<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Transform;

/**
 * Removes duplicate values.
 */
final readonly class UniqueValues
{
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(): array
    {
        return $this->unique();
    }

    public function unique(): array
    {
        return array_unique(array: $this->items, flags: SORT_REGULAR);
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
