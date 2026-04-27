<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Operators\Strings;

/**
 * Converts all collection values to uppercase.
 */
final readonly class UppercaseValues
{
    public function __construct(private array $items = []) {}

    public function __invoke() : array
    {
        return array_map('mb_strtoupper', $this->items);
    }
}
