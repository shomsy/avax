<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Operators\Search;

/**
 * Checks if a value exists in the collection.
 */
final readonly class ContainsValue
{
    public function __construct(private array $items = []) {}

    public function __invoke(mixed $value, bool $strict = true) : bool
    {
        return in_array($value, $this->items, $strict);
    }
}
