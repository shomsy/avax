<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform;

/**
 * Groups collection items by key.
 */
final readonly class GroupValues
{
    public function __construct(
        private array $items = [],
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function __invoke(string|callable $key): array
    {
        return $this->group(key: $key);
    }

    /**
     * @return array<mixed>
     */
    public function group(string|callable $key): array
    {
        $grouped = [];

        foreach ($this->items as $item) {
            $groupKey = is_callable(value: $key) ? $key($item) : ($item[$key] ?? null);
            $grouped[$groupKey][] = $item;
        }

        return $grouped;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
