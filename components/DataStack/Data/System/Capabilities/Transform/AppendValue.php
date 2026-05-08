<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Transform;

/**
 * Appends a value to the end of collection.
 */
final readonly class AppendValue
{
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(mixed $value): array
    {
        return $this->append(value: $value);
    }

    public function append(mixed $value): array
    {
        $items = $this->items;
        $items[] = $value;

        return $items;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
