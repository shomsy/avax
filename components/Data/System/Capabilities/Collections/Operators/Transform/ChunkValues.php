<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Chunks the collection into multiple, smaller collections of a given size.
 */
final readonly class ChunkValues
{
    public function __construct(private array $items = []) {}

    public function __invoke(int $size, bool $preserveKeys = false) : array
    {
        return array_chunk($this->items, $size, $preserveKeys);
    }
}
