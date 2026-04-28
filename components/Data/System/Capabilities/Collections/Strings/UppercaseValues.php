<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Strings;

/**
 * Converts all collection values to uppercase.
 */
final readonly class UppercaseValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->uppercase();
    }

    public function uppercase() : array
    {
        return array_map(
            callback: static fn (mixed $value) : string => strtoupper(string: (string) $value),
            array   : $this->items
        );
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
