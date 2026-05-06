<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Maps a callback over collection items.
 */
final readonly class MapValues
{
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(callable $callback): array
    {
        return array_map(callback: $callback, array: $this->items);
    }
}
