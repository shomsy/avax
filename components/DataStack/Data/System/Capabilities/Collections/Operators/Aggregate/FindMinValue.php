<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Aggregate;

/**
 * Finds the minimum value in a collection.
 */
final readonly class FindMinValue
{
    public function __construct(private array $items = []) {}

    public function __invoke() : mixed
    {
        return empty($this->items) ? null : min($this->items);
    }
}
