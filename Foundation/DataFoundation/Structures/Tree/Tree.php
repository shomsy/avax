<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Structures\Tree;

use Avax\DataFoundation\Exceptions\InvalidStructureException;

/**
 * Immutable tree node with named children.
 */
final readonly class Tree
{
    /**
     * @param array<int, self> $children
     */
    public function __construct(
        private mixed $value,
        private array $children = [],
    ) {}

    public function value() : mixed
    {
        return $this->value;
    }

    /**
     * @return array<int, self>
     */
    public function children() : array
    {
        return $this->children;
    }

    public function addChild(self $child) : self
    {
        if ($child === $this) {
            throw InvalidStructureException::treeNodeCannotReferenceItself();
        }

        $children   = $this->children;
        $children[] = $child;

        return new self(value: $this->value, children: $children);
    }

    public function map(callable $callback) : self
    {
        $children = array_map(
            static fn (self $child) : self => $child->map(callback: $callback),
            $this->children
        );

        return new self(value: $callback($this->value), children: $children);
    }

    public function toArray() : array
    {
        return [
            'value'    => $this->value,
            'children' => array_map(
                static fn (self $child) : array => $child->toArray(),
                $this->children
            ),
        ];
    }
}
