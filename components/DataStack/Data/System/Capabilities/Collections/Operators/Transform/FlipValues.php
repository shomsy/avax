<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Swaps collection keys with their corresponding values.
 */
final readonly class FlipValues
{
    public function __construct(private array $items = []) {}

    public function __invoke() : array
    {
        return array_flip($this->items);
    }
}
