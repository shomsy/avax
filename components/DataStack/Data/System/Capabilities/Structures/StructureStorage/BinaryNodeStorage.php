<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

/**
 * BinaryNodeStorage — immutable binary tree node for tree-based structures.
 *
 * @template T
 */
final readonly class BinaryNodeStorage
{
    /**
     * @param T            $value
     * @param self<T>|null $left
     * @param self<T>|null $right
     */
    public function __construct(
        public mixed     $value,
        public self|null $left = null,
        public self|null $right = null,
    ) {}

    /**
     * Return a new node with the given left child.
     *
     * @param self<T>|null $left
     *
     * @return self<T>
     */
    public function withLeft(self|null $left) : self
    {
        return new self(value: $this->value, left: $left, right: $this->right);
    }

    /**
     * Return a new node with the given right child.
     *
     * @param self<T>|null $right
     *
     * @return self<T>
     */
    public function withRight(self|null $right) : self
    {
        return new self(value: $this->value, left: $this->left, right: $right);
    }

    /**
     * Return a new node with the given value.
     *
     * @param T $value
     *
     * @return self<T>
     */
    public function withValue(mixed $value) : self
    {
        return new self(value: $value, left: $this->left, right: $this->right);
    }

    public function count() : int
    {
        $count = 1;
        if ($this->left !== null) {
            $count += $this->left->count();
        }
        if ($this->right !== null) {
            $count += $this->right->count();
        }

        return $count;
    }

    /**
     * @return list<mixed>
     */
    public function inOrder() : array
    {
        $result = [];
        if ($this->left !== null) {
            $result = [...$result, ...$this->left->inOrder()];
        }
        $result[] = $this->value;
        if ($this->right !== null) {
            $result = [...$result, ...$this->right->inOrder()];
        }

        return $result;
    }

    /**
     * @return list<mixed>
     */
    public function preOrder() : array
    {
        $result = [$this->value];
        if ($this->left !== null) {
            $result = [...$result, ...$this->left->preOrder()];
        }
        if ($this->right !== null) {
            $result = [...$result, ...$this->right->preOrder()];
        }

        return $result;
    }

    /**
     * @return list<mixed>
     */
    public function postOrder() : array
    {
        $result = [];
        if ($this->left !== null) {
            $result = [...$result, ...$this->left->postOrder()];
        }
        if ($this->right !== null) {
            $result = [...$result, ...$this->right->postOrder()];
        }
        $result[] = $this->value;

        return $result;
    }
}
