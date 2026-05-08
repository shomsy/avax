<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

/**
 * Iterates over each item with a side-effect callback, returning the original items.
 */
final readonly class EachValues
{
    /**
     * @param  array<mixed>  $items
     */
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /**
     * @param  callable  $callback  fn(mixed $item, int|string $key) : void
     * @return array<mixed>
     */
    public function __invoke(callable $callback): array
    {
        return $this->each(callback: $callback);
    }

    /**
     * @param  callable  $callback  fn(mixed $item, int|string $key) : void
     * @return array<mixed>
     */
    public function each(callable $callback): array
    {
        $items = $this->items;
        array_walk(array: $items, callback: $callback);

        return $this->items;
    }

    /**
     * @return array<mixed>
     */
    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
