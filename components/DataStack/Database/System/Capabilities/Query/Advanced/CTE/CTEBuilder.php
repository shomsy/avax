<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\CTE;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;

final class CTEBuilder
{
    private array $ctes = [];

    public function with(string $name, QueryState $queryState): self
    {
        $this->ctes[$name] = [
            'query' => $queryState,
            'type' => 'simple',
        ];

        return $this;
    }

    public function withRecursive(string $name, QueryState $initialQuery, QueryState $recursiveQuery): self
    {
        $this->ctes[$name] = [
            'initial' => $initialQuery,
            'recursive' => $recursiveQuery,
            'type'    => 'recursive',
        ];

        return $this;
    }

    public function getSQL(): string
    {
        $definitions = [];
        $isRecursive = false;
        foreach ($this->ctes as $name => $cte) {
            if ($cte['type'] === 'recursive') {
                $definitions[] = sprintf('%s AS (%s UNION ALL %s)', $name, $this->buildCTE(query: $cte['initial']), $this->buildCTE(query: $cte['recursive']));
                $isRecursive = true;
            } else {
                $definitions[] = sprintf('%s AS (%s)', $name, $this->buildCTE(query: $cte['query']));
            }
        }
        if ($definitions === []) {
            return '';
        }

        return 'WITH ' . ($isRecursive ? 'RECURSIVE ' : '') . implode(separator: ', ', array: $definitions);
    }

    private function buildCTE(QueryState $queryState): string
    {
        $columns = implode(separator: ', ', array: $queryState->columns !== [] ? $queryState->columns : ['*']);

        return sprintf('SELECT %s FROM %s', $columns, $queryState->from);
    }

    public function getCTEs(): array
    {
        return $this->ctes;
    }
}
