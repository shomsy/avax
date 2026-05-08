<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Selection;

use Avax\Components\DataStack\Data\System\Capabilities\Lenses\DataPath\DotPath;

/**
 * Checks if a key or path exists.
 */
final readonly class HasValue
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(string $key): bool
    {
        return $this->check(key: $key);
    }

    public function check(string $key): bool
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return true;
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return new DotPath(path: $key)->exists(items: $this->items);
        }

        return false;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
