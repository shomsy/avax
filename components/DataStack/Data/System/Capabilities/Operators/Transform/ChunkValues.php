<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

use InvalidArgumentException;

/**
 * Chunks collection into groups.
 */
final readonly class ChunkValues
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(int $size): array
    {
        return $this->chunk(size: $size);
    }

    /** @return array<array-key, mixed> */
    public function chunk(int $size): array
    {
        if ($size <= 0) {
            throw new InvalidArgumentException(message: 'Chunk size must be greater than 0.');
        }

        return array_chunk(array: $this->items, length: $size, preserve_keys: true);
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
