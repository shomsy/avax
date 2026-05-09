<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees;

use Countable;
use Generator;
use Override;

final readonly class BinarySearchTree implements Countable
{
    public function __construct(private BinaryTree $tree) {}

    public static function empty() : self
    {
        return new self(tree: BinaryTree::empty());
    }

    public function insert(int|string $value) : self
    {
        return new self(tree: $this->insertInto(tree: $this->tree, value: $value));
    }

    private function insertInto(BinaryTree $tree, int|string $value) : BinaryTree
    {
        if ($tree->isEmpty()) {
            return BinaryTree::node(value: $value);
        }

        /** @var int|string $current */
        $current = $tree->value();

        if ($value === $current) {
            return $tree;
        }

        if ($value < $current) {
            return BinaryTree::node(value: $current, left: $this->insertInto(tree: $tree->left(), value: $value), right: $tree->right());
        }

        return BinaryTree::node(value: $current, left: $tree->left(), right: $this->insertInto(tree: $tree->right(), value: $value));
    }

    public function isEmpty() : bool
    {
        return $this->tree->isEmpty();
    }

    public function contains(int|string $value) : bool
    {
        $node = $this->tree;

        while ( ! $node->isEmpty() ) {
            $current = $node->value();

            if ($current === $value) {
                return true;
            }

            $node = $value < $current ? $node->left() : $node->right();
        }

        return false;
    }

    /**
     * @return list<int|string>
     */
    public function toArray() : array
    {
        /** @var list<int|string> $values */
        $values = iterator_to_array(iterator: $this->traverseInOrder(), preserve_keys: false);

        return $values;
    }

    /**
     * @return Generator<int, int|string>
     */
    public function traverseInOrder() : Generator
    {
        yield from $this->tree->traverseInOrder();
    }

    #[Override]
    public function count() : int
    {
        return $this->tree->count();
    }
}
