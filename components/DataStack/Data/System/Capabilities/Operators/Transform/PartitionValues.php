<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

/**
 * Partitions collection into two groups by callback.
 */
final readonly class PartitionValues
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /**
     * @param callable(mixed): bool $callback
     * @return array{array<array-key, mixed>, array<array-key, mixed>}
     */
    public function __invoke(callable $callback): array
    {
        return $this->partition(callback: $callback);
    }

    /**
     * @param callable(mixed): bool $callback
     * @return array{array<array-key, mixed>, array<array-key, mixed>}
     */
    public function partition(callable $callback): array
    {
        $pass = [];
        $fail = [];

        foreach ($this->items as $item) {
            if ($callback($item)) {
                $pass[] = $item;
            } else {
                $fail[] = $item;
            }
        }

        return [$pass, $fail];
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
