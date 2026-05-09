<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

/**
 * WeightedEdge — an edge with an associated weight or cost.
 */
final readonly class WeightedEdge extends Edge
{
    public function __construct(
        int|string       $from,
        int|string       $to,
        public float|int $weight,
    )
    {
        parent::__construct(from: $from, to: $to);
    }

    /**
     * @return array{from: int|string, to: int|string, weight: float|int}
     */
    public function toArray() : array
    {
        return ['from' => $this->from, 'to' => $this->to, 'weight' => $this->weight];
    }
}
