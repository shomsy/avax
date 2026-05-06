<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance;

final class QueryReport
{
    /** @var array<string, int> */
    public array $nPlusOne = [];

    /** @var list<array{query: string, duration: float}> */
    public array $slowQueries = [];

    /** @var list<string> */
    public array $bindingViolations = [];

    public function addNPlusOne(string $location, int $count): void
    {
        $this->nPlusOne[$location] = $count;
    }

    public function addSlowQuery(string $query, float $duration): void
    {
        $this->slowQueries[] = ['query' => $query, 'duration' => $duration];
    }

    public function addBindingViolation(string $location): void
    {
        $this->bindingViolations[] = $location;
    }

    /**
     * @return array{n_plus_one: array<string, int>, slow_queries: list<array{query: string, duration: float}>,
     *                           binding_violations: list<string>}
     */
    public function toArray(): array
    {
        return [
            'n_plus_one' => $this->nPlusOne,
            'slow_queries' => $this->slowQueries,
            'binding_violations' => $this->bindingViolations,
        ];
    }
}
