<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Write;

use Avax\Components\Data\System\Capabilities\Collections\Internal\Paths\DotPath;

/**
 * Forgets (removes) a value by key or path.
 */
final readonly class ForgetValue
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $key) : array
    {
        return $this->forget(key: $key);
    }

    public function forget(string $key) : array
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

    public function getItems() : array
    {
        return $this->items;
    }
}
