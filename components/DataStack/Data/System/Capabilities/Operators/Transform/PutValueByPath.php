<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Transform;

use Avax\Components\DataStack\Data\System\Capabilities\Lenses\DataPath\DotPath;

/**
 * Puts a value by dot notation path.
 */
final readonly class PutValueByPath
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(string $path, mixed $value): array
    {
        return $this->put(path: $path, value: $value);
    }

    /** @return array<array-key, mixed> */
    public function put(string $path, mixed $value): array
    {
        $items = $this->items;
        new DotPath(path: $path)->setValue(items: $items, value: $value);

        return $items;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
