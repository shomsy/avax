<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Strings;

/**
 * Joins collection values into a string.
 */
final readonly class JoinValues
{
    public function __construct(private array $items = []) {}

    public function __invoke(string $glue = '') : string
    {
        return implode($glue, $this->items);
    }
}
