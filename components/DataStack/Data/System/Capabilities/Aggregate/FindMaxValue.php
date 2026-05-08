<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Aggregate;

use LogicException;

/**
 * Finds maximum value by key.
 */
final readonly class FindMaxValue
{
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(string|callable $key): mixed
    {
        return $this->max(key: $key);
    }

    public function max(string|callable $key): mixed
    {
        if ($this->items === []) {
            throw new LogicException(message: 'Cannot find maximum of empty collection.');
        }

        $values = array_map(
            callback: static fn (mixed $item): mixed => is_callable(value: $key) ? $key($item) : ($item[$key] ?? null),
            array   : $this->items,
        );

        return max(value: $values);
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
