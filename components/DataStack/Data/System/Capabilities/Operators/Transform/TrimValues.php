<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

/**
 * Trims whitespace from all collection values.
 */
final readonly class TrimValues
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(string $characters = " \n\r\t\v\x00"): array
    {
        return $this->trim(characters: $characters);
    }

    /** @return array<array-key, mixed> */
    public function trim(string $characters = " \n\r\t\v\x00"): array
    {
        return array_map(
            callback: static fn (mixed $value): string => trim(string: (string) $value, characters: $characters),
            array   : $this->items,
        );
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
