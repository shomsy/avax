<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Operators\Search;

/**
 * Searches for a value and returns its key.
 */
final readonly class SearchValue
{
    public function __construct(private array $items = []) {}

    public function __invoke(mixed $value, bool $strict = true) : string|int|false
    {
        return array_search($value, $this->items, $strict);
    }
}
