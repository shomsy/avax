<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Write;

use Avax\Components\Data\System\Capabilities\Collections\Internal\Paths\DotPath;

/**
 * Puts a value by dot notation path.
 */
final readonly class PutValueByPath
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $path, mixed $value) : array
    {
        return $this->put(path: $path, value: $value);
    }

    public function put(string $path, mixed $value) : array
    {
        $items = $this->items;
        new DotPath(path: $path)->setValue(items: $items, value: $value);

        return $items;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
