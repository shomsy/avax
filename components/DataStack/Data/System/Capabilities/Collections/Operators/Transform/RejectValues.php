<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Filters the collection using a callback, rejecting items that pass the truth test.
 */
final readonly class RejectValues
{
    public function __construct(private array $items = []) {}

    public function __invoke(callable $callback): array
    {
        return array_filter($this->items, static fn ($value, $key): bool => ! $callback($value, $key), ARRAY_FILTER_USE_BOTH);
    }
}
