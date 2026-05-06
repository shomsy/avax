<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Iteratively reduces the collection to a single value using a callback.
 */
final readonly class ReduceValues
{
    public function __construct(private array $items = [])
    {
    }

    public function __invoke(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }
}
