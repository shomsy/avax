<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Operators\Strings;

/**
 * Converts all collection values to lowercase.
 */
final readonly class LowercaseValues
{
    public function __construct(private array $items = []) {}

    public function __invoke() : array
    {
        return array_map('mb_strtolower', $this->items);
    }
}
