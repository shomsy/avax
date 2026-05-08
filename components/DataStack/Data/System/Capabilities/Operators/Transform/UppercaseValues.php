<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

/**
 * Converts all collection values to uppercase.
 */
final readonly class UppercaseValues
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(): array
    {
        return $this->uppercase();
    }

    /** @return array<array-key, mixed> */
    public function uppercase(): array
    {
        return array_map(
            callback: static fn (mixed $value): string => strtoupper(string: (string) $value),
            array   : $this->items,
        );
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
