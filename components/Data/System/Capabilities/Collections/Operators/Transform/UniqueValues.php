<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Returns all unique items in the collection.
 */
final readonly class UniqueValues
{
    public function __construct(private array $items = []) {}

    public function __invoke(int $flags = SORT_STRING) : array
    {
        return array_unique($this->items, $flags);
    }
}
