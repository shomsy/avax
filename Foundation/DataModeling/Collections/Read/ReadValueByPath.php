<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Read;

use Avax\DataModeling\Collections\Internal\DotPath;

/**
 * Reads a value by dot notation path.
 */
final readonly class ReadValueByPath
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $path, mixed $default = null) : mixed
    {
        return $this->get(path: $path, default: $default);
    }

    public function get(string $path, mixed $default = null) : mixed
    {
        $dotPath = new DotPath(path: $path);

        return $dotPath->getValue(items: $this->items, default: $default);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}