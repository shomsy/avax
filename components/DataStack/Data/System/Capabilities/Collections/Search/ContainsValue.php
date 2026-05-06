<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Search;

/**
 * Checks if collection contains a value.
 */
final readonly class ContainsValue
{
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(mixed $value): bool
    {
        return $this->contains(value: $value);
    }

    public function contains(mixed $value): bool
    {
        return in_array(needle: $value, haystack: $this->items, strict: true);
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
