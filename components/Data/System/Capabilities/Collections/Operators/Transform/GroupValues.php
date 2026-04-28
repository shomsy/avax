<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Operators\Transform;

/**
 * Groups the collection items by a given key or callback.
 */
final readonly class GroupValues
{
    public function __construct(private array $items = []) {}

    public function __invoke(callable|string $groupBy) : array
    {
        $results = [];
        foreach ($this->items as $key => $value) {
            $groupKey             = is_callable($groupBy) ? $groupBy($value, $key) : ($value[$groupBy] ?? null);
            $results[$groupKey][] = $value;
        }

        return $results;
    }
}
