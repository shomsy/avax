<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

/**
 * Pair — an ordered two-element value object.
 *
 * Used as a building block for map entries, graph edges, and tree nodes.
 */
final readonly class Pair
{
    public function __construct(
        public mixed $first,
        public mixed $second,
    ) {}

    public function swap() : self
    {
        return new self(first: $this->second, second: $this->first);
    }

    /**
     * @return array{0: mixed, 1: mixed}
     */
    public function toArray() : array
    {
        return [$this->first, $this->second];
    }
}
