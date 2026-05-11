<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Avax\Components\DataStack\Data\System\Foundation\Failure\MissingKey;

/**
 * TreeNodeStorage — immutable n-ary tree node with named or indexed children.
 *
 * @template T
 */
final readonly class TreeNodeStorage
{
    /**
     * @param T             $value
     * @param list<self<T>> $children
     */
    public function __construct(
        public mixed $value,
        public array $children = [],
    ) {}

    /**
     * Return a new node with the given children.
     *
     * @param list<self<T>> $children
     *
     * @return self<T>
     */
    public function withChildren(array $children) : self
    {
        return new self(value: $this->value, children: $children);
    }

    /**
     * Return a new node with an additional child appended.
     *
     * @param self<T> $child
     *
     * @return self<T>
     */
    public function addChild(self $child) : self
    {
        return new self(value: $this->value, children: [...$this->children, $child]);
    }

    public function count() : int
    {
        $count = 1;
        foreach ($this->children as $child) {
            $count += $child->count();
        }

        return $count;
    }

    public function depth() : int
    {
        if ($this->children === []) {
            return 1;
        }

        $maxDepth = 0;
        foreach ($this->children as $child) {
            $maxDepth = max($maxDepth, $child->depth());
        }

        return 1 + $maxDepth;
    }

    /**
     * @return list<mixed>
     */
    public function preOrder() : array
    {
        $result = [$this->value];
        foreach ($this->children as $child) {
            $result = [...$result, ...$child->preOrder()];
        }

        return $result;
    }

    /**
     * @return list<mixed>
     */
    public function postOrder() : array
    {
        $result = [];
        foreach ($this->children as $child) {
            $result = [...$result, ...$child->postOrder()];
        }
        $result[] = $this->value;

        return $result;
    }

    /**
     * @return list<mixed>
     */
    public function levelOrder() : array
    {
        $result = [];
        $queue  = [$this];
        while ( $queue !== [] ) {
            $node     = array_shift($queue);
            $result[] = $node->value;
            foreach ($node->children as $child) {
                $queue[] = $child;
            }
        }

        return $result;
    }

    /**
     * Check if a value exists in the tree using a comparison callback.
     */
    public function contains(mixed $value, callable|null $compare = null) : bool
    {
        $compare ??= static fn (mixed $a, mixed $b) : bool => $a === $b;

        if ($compare($this->value, $value)) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child->contains(value: $value, compare: $compare)) {
                return true;
            }
        }

        return false;
    }
}
