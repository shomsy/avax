<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Aggregate;

/**
 * Finds the maximum value in a collection.
 */
final readonly class FindMaxValue
{
    public function __construct(private array $items = []) {}

    public function __invoke(): mixed
    {
        return $this->items === [] ? null : max($this->items);
    }
}
