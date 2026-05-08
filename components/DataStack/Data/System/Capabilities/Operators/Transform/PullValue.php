<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

/**
 * Pulls (removes and returns) a value by key.
 */
final readonly class PullValue
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(string $key): mixed
    {
        return $this->pull(key: $key);
    }

    public function pull(string $key): mixed
    {
        $value = $this->items[$key] ?? null;

        if (array_key_exists(key: $key, array: $this->items)) {
            $items = $this->items;
            unset($items[$key]);

            return [$value, $items];
        }

        return [$value, $this->items];
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
