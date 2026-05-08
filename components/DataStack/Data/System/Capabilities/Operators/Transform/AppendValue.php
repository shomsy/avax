<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

/**
 * Appends a value to the end of collection.
 */
final readonly class AppendValue
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(mixed $value): array
    {
        return $this->append(value: $value);
    }

    /** @return array<array-key, mixed> */
    public function append(mixed $value): array
    {
        $items = $this->items;
        $items[] = $value;

        return $items;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
