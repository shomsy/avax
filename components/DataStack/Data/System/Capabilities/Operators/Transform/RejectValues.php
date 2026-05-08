<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

/**
 * Rejects items that pass the truth test — the inverse of filter.
 */
final readonly class RejectValues
{
    /**
     * @param array<mixed> $items
     */
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param callable $callback fn(mixed $item, int|string $key) : bool
     *
     * @return array<mixed>
     */
    public function __invoke(callable $callback) : array
    {
        return $this->reject(callback: $callback);
    }

    /**
     * @param callable $callback fn(mixed $item, int|string $key) : bool
     *
     * @return array<mixed>
     */
    public function reject(callable $callback) : array
    {
        return array_filter(
            array   : $this->items,
            callback: static fn (mixed $value, mixed $key) : bool => ! $callback($value, $key),
            mode    : ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * @return array<mixed>
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
