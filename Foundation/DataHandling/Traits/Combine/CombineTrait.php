<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Combine;

use InvalidArgumentException;

/**
 * Provides set operations: merge, union, diff, intersect.
 */
trait CombineTrait
{
    abstract protected function getItems(): array;

    abstract protected function withItems(array $items): static;

    public function merge(array ...$items): static
    {
        return $this->withItems(array_merge($this->getItems(), ...$items));
    }

    public function union(array $items): static
    {
        return $this->withItems($this->getItems() + $items);
    }

    public function diff(array $items): static
    {
        return $this->withItems(array_diff($this->getItems(), $items));
    }

    public function intersect(array $items): static
    {
        return $this->withItems(array_intersect($this->getItems(), $items));
    }

    public function intersectByKey(array $keys): static
    {
        return $this->withItems(array_intersect_key($this->getItems(), array_flip($keys)));
    }

    public function diffAssoc(array $items): static
    {
        return $this->withItems(array_diff_assoc($this->getItems(), $items));
    }

    public function intersectAssoc(array $items): static
    {
        return $this->withItems(array_intersect_assoc($this->getItems(), $items));
    }

    public function replaceRecursive(array $items): static
    {
        return $this->withItems(array_replace_recursive($this->getItems(), $items));
    }

    public function replaceRecursiveIndex(array $items, int $offset): static
    {
        $result = $this->getItems();
        array_splice($result, $offset, count($items), $items);

        return $this->withItems($result);
    }
}