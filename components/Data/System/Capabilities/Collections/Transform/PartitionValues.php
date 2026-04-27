<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Transform;

/**
 * Partitions collection into two groups by callback.
 */
final readonly class PartitionValues
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param callable $callback fn(mixed $item): bool
     *
     * @return array{0: array, 1: array}
     */
    public function __invoke(callable $callback) : array
    {
        return $this->partition(callback: $callback);
    }

    /**
     * @param callable $callback fn(mixed $item): bool
     *
     * @return array{0: array, 1: array}
     */
    public function partition(callable $callback) : array
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

    public function getItems() : array
    {
        return $this->items;
    }
}
