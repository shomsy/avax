<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Transform;

/**
 * Flattens nested arrays to specified depth.
 */
final readonly class FlattenValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(int $depth = -1): array
    {
        return $this->flatten(depth: $depth);
    }

    public function flatten(int $depth = -1): array
    {
        $result = [];

        foreach ($this->items as $item) {
            if (is_array(value: $item)) {
                foreach ($item as $value) {
                    $result[] = $value;
                }
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}