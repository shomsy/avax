<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Graphs;

use Countable;
use Override;

final class UnionFind implements Countable
{
    /** @var array<array-key, array-key> */
    private array $parent = [];

    /** @var array<array-key, int> */
    private array $rank = [];

    /**
     * @param iterable<array-key> $items
     */
    public function __construct(iterable $items = [])
    {
        foreach ($items as $item) {
            $this->add(item: $item);
        }
    }

    public function add(int|string $item) : void
    {
        if (! array_key_exists(key: $item, array: $this->parent)) {
            $this->parent[$item] = $item;
            $this->rank[$item]   = 0;
        }
    }

    public function union(int|string $left, int|string $right) : void
    {
        $this->add(item: $left);
        $this->add(item: $right);

        $leftRoot  = $this->find(item: $left);
        $rightRoot = $this->find(item: $right);

        if ($leftRoot === $rightRoot) {
            return;
        }

        if ($this->rank[$leftRoot] < $this->rank[$rightRoot]) {
            $this->parent[$leftRoot] = $rightRoot;

            return;
        }

        if ($this->rank[$leftRoot] > $this->rank[$rightRoot]) {
            $this->parent[$rightRoot] = $leftRoot;

            return;
        }

        $this->parent[$rightRoot] = $leftRoot;
        $this->rank[$leftRoot]++;
    }

    public function find(int|string $item) : int|string
    {
        $this->add(item: $item);

        if ($this->parent[$item] !== $item) {
            $this->parent[$item] = $this->find(item: $this->parent[$item]);
        }

        return $this->parent[$item];
    }

    public function connected(int|string $left, int|string $right) : bool
    {
        $this->add(item: $left);
        $this->add(item: $right);

        return $this->find(item: $left) === $this->find(item: $right);
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->parent);
    }
}
