<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Strings;

/**
 * Converts all collection values to lowercase.
 */
final readonly class LowercaseValues
{
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(): array
    {
        return $this->lowercase();
    }

    public function lowercase(): array
    {
        return array_map(
            callback: static fn (mixed $value): string => strtolower(string: (string) $value),
            array   : $this->items,
        );
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
