<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Strings;

/**
 * Joins collection values into a string.
 */
final readonly class JoinValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $separator = '') : string
    {
        return $this->join(separator: $separator);
    }

    public function join(string $separator = '') : string
    {
        return implode(separator: $separator, array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
