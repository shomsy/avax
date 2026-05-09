<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees;

use Countable;
use Generator;
use Override;

final readonly class BinaryTree implements Countable
{
    public function __construct(
        private mixed           $value = null,
        private BinaryTree|null $left = null,
        private BinaryTree|null $right = null,
        private bool            $empty = true,
    ) {}

    public static function node(mixed $value, BinaryTree|null $left = null, BinaryTree|null $right = null) : self
    {
        return new self(value: $value, left: $left, right: $right, empty: false);
    }

    public function value(mixed $default = null) : mixed
    {
        if ($this->empty) {
            return $default;
        }

        return $this->value;
    }

    /**
     * @return Generator<int, mixed>
     */
    public function traverseInOrder() : Generator
    {
        if ($this->empty) {
            return;
        }

        yield from $this->left()->traverseInOrder();
        yield $this->value;
        yield from $this->right()->traverseInOrder();
    }

    public function left() : self
    {
        return $this->left ?? self::empty();
    }

    public static function empty() : self
    {
        return new self();
    }

    public function right() : self
    {
        return $this->right ?? self::empty();
    }

    public function isEmpty() : bool
    {
        return $this->empty;
    }

    #[Override]
    public function count() : int
    {
        if ($this->empty) {
            return 0;
        }

        return 1 + $this->left()->count() + $this->right()->count();
    }
}
