<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\Window;

use ArrayIterator;
use Avax\Components\Data\Exceptions\InvalidFlowException;
use Avax\Components\Data\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Sliding windows over ordered input.
 */
final readonly class Window implements IteratorAggregate, Countable
{
    /**
     * @param array<int, array<int, mixed>> $windows
     */
    private function __construct(
        private array $windows,
    ) {}

    public static function from(iterable $items, int $size, int $step = 1) : self
    {
        if ($size <= 0) {
            throw InvalidFlowException::invalidWindowSize(size: $size);
        }

        if ($step <= 0) {
            throw InvalidFlowException::invalidWindowSize(size: $step);
        }

        $source  = array_values(NormalizedIterable::toArrayPreserveKeys(iterable: $items));
        $windows = [];

        for ($index = 0; $index < count($source); $index += $step) {
            $window = array_slice($source, $index, $size);

            if (count($window) < $size) {
                break;
            }

            $windows[] = $window;
        }

        return new self(windows: $windows);
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function all() : array
    {
        return $this->windows;
    }

    public function count() : int
    {
        return count($this->windows);
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->windows);
    }
}
