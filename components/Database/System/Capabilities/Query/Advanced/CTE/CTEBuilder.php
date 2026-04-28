<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\Advanced\CTE;

use Avax\Components\Database\System\Capabilities\Query\State\QueryState;

final class CTEBuilder
{
    private array $ctes = [];

    public function with(string $name, QueryState $initialQuery) : self
    {
        $this->ctes[$name] = [
            'query' => $initialQuery,
            'type'  => 'simple',
        ];

        return $this;
    }

    public function withRecursive(string $name, QueryState $initialQuery, QueryState $recursiveQuery) : self
    {
        $this->ctes[$name] = [
            'initial'   => $initialQuery,
            'recursive' => $recursiveQuery,
            'type'      => 'recursive',
        ];

        return $this;
    }

    public function getSQL(string $dialect) : string
    {
        $definitions = [];
        $isRecursive = false;

        foreach ($this->ctes as $name => $cte) {
            if ($cte['type'] === 'recursive') {
                $definitions[] = "{$name} AS ({$this->buildCTE(query: $cte['initial'])} UNION ALL {$this->buildCTE(query: $cte['recursive'])})";
                $isRecursive   = true;
            } else {
                $definitions[] = "{$name} AS ({$this->buildCTE(query: $cte['query'])})";
            }
        }

        if ($definitions === []) {
            return '';
        }

        return 'WITH ' . ($isRecursive ? 'RECURSIVE ' : '') . implode(separator: ', ', array: $definitions);
    }

    private function buildCTE(QueryState $query) : string
    {
        $columns = implode(separator: ', ', array: $query->columns ?: ['*']);

        return "SELECT {$columns} FROM {$query->from}";
    }

    public function getCTEs() : array
    {
        return $this->ctes;
    }
}
