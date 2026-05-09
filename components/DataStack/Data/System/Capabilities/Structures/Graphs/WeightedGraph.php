<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Graphs;

use Countable;
use Override;

final readonly class WeightedGraph implements Countable
{
    /**
     * @param array<array-key, array<array-key, int|float>> $adjacency
     */
    public function __construct(private array $adjacency = [], private bool $directed = false) {}

    public function addEdge(int|string $from, int|string $to, int|float $weight) : self
    {
        $adjacency             = $this->adjacency;
        $adjacency[$from]      ??= [];
        $adjacency[$to]        ??= [];
        $adjacency[$from][$to] = $weight;

        if (! $this->directed) {
            $adjacency[$to][$from] = $weight;
        }

        return new self(adjacency: $adjacency, directed: $this->directed);
    }

    public function weightOf(int|string $from, int|string $to, int|float|null $default = null) : int|float|null
    {
        return $this->adjacency[$from][$to] ?? $default;
    }

    public function hasEdge(int|string $from, int|string $to) : bool
    {
        return array_key_exists(key: $to, array: $this->adjacency[$from] ?? []);
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->adjacency);
    }
}
