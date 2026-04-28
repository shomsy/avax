<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Strings;

/**
 * Trims whitespace from all collection values.
 */
final readonly class TrimValues
{
    public function __construct(private array $items = []) {}

    public function __invoke(string $characters = " \t\n\r\0\x0B") : array
    {
        return array_map(fn ($value) => is_string($value) ? trim($value, $characters) : $value, $this->items);
    }
}
