<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

/**
 * Edge — a connection between two nodes in a graph.
 */
readonly class Edge
{
    public function __construct(
        public int|string $from,
        public int|string $to,
    ) {}

    public function isSelfLoop() : bool
    {
        return $this->from === $this->to;
    }

    public function reverse() : self
    {
        return new self(from: $this->to, to: $this->from);
    }

    /**
     * @return array{from: int|string, to: int|string}
     */
    public function toArray() : array
    {
        return ['from' => $this->from, 'to' => $this->to];
    }
}
