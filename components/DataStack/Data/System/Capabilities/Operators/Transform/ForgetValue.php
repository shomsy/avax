<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

use Avax\Components\DataStack\Data\System\Capabilities\Lenses\DataPath\DotPath;

/**
 * Forgets (removes) a value by key or path.
 */
final readonly class ForgetValue
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(string $key): array
    {
        return $this->forget(key: $key);
    }

    /** @return array<array-key, mixed> */
    public function forget(string $key): array
    {
        $items = $this->items;

        if (array_key_exists(key: $key, array: $items)) {
            unset($items[$key]);

            return $items;
        }

        if (str_contains(haystack: $key, needle: '.')) {
            $dotPath = new DotPath(path: $key);
            $dotPath->unsetValue(items: $items);

            return $items;
        }

        return $items;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
