<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Transform;

/**
 * Rejects collection items by callback.
 */
final readonly class RejectValues
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param callable $callback fn(mixed $item): bool
     * @return array<mixed>
     */
    public function __invoke(callable $callback): array
    {
        return $this->reject(callback: $callback);
    }

    /**
     * @param callable $callback fn(mixed $item): bool
     * @return array<mixed>
     */
    public function reject(callable $callback): array
    {
        $filter = fn(mixed $item): bool => ! $callback($item);

        return array_filter(array: $this->items, callback: $filter);
    }

    public function getItems(): array
    {
        return $this->items;
    }
}