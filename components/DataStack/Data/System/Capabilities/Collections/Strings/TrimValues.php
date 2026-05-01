<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Strings;

/**
 * Trims whitespace from all collection values.
 */
final readonly class TrimValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $characters = " \n\r\t\v\x00"): array
    {
        return $this->trim(characters: $characters);
    }

    public function trim(string $characters = " \n\r\t\v\x00"): array
    {
        return array_map(
            callback: static fn (mixed $value): string => trim(string: (string) $value, characters: $characters),
            array   : $this->items,
        );
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
