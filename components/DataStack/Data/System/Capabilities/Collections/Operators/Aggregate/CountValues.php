<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Aggregate;

/**
 * Counts values.
 */
final readonly class CountValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(): int
    {
        return count($this->items);
    }
}
