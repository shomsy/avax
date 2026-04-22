<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Advanced\CTE;

use Avax\Database\System\Capabilities\Query\State\QueryState;

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
        $sql = [];

        foreach ($this->ctes as $name => $cte) {
            if ($cte['type'] === 'recursive') {
                $sql[] = "WITH RECURSIVE {$name} AS ({$this->buildCTE(query:$cte['initial'])} UNION ALL {$this->buildCTE(query:$cte['recursive'])})";
            } else {
                $sql[] = "WITH {$name} AS ({$this->buildCTE(query:$cte['query'])})";
            }
        }

        return implode(separator: ', ', array: $sql);
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
