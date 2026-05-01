<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry;

final class N1QueryDetector
{
    private array $queryLog = [];

    private int $queryCount = 0;

    private array $tableAccessLog = [];

    public function record(string $table, string $query): void
    {
        $this->queryCount++;
        $this->queryLog[] = [
            'table' => $table,
            'query' => $query,
            'timestamp' => microtime(as_float: true),
        ];

        if (! isset($this->tableAccessLog[$table])) {
            $this->tableAccessLog[$table] = 0;
        }

        $this->tableAccessLog[$table]++;
    }

    public function getReport(): array
    {
        return [
            'total_queries' => $this->queryCount,
            'table_access' => $this->tableAccessLog,
            'is_n1_problem' => $this->isN1Problem(),
            'patterns'     => $this->detectPatterns(),
        ];
    }

    public function isN1Problem(int $threshold = 10): bool
    {
        $uniqueTables = count(value: $this->tableAccessLog);

        return $this->queryCount > $threshold * $uniqueTables;
    }

    public function detectPatterns(): array
    {
        $patterns = [];

        foreach ($this->tableAccessLog as $table => $count) {
            if ($count > 10) {
                $patterns[] = [
                    'type'       => 'N+1',
                    'table'      => $table,
                    'query_count' => $count,
                    'suggestion' => 'Use eager loading or DataLoader for ' . $table,
                ];
            }
        }

        return $patterns;
    }

    public function reset(): void
    {
        $this->queryLog   = [];
        $this->queryCount = 0;
        $this->tableAccessLog = [];
    }
}
