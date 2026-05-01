<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\OpenTelemetry\QuerySpan;

final class DbMetricsCollector
{
    private int $queryCount = 0;

    private float $totalDurationMs = 0.0;

    private int $errorCount = 0;

    /** @var array<string, int> */
    private array $byConnection = [];

    public function record(QuerySpan $span): void
    {
        $this->queryCount++;
        $this->totalDurationMs += $span->getDurationMs();

        if ($span->error !== null) {
            $this->errorCount++;
        }

        $connection = $span->connection ?? 'default';
        $this->byConnection[$connection] = ($this->byConnection[$connection] ?? 0) + 1;
    }

    public function snapshot(): array
    {
        return [
            'query_count'       => $this->queryCount,
            'total_duration_ms' => $this->totalDurationMs,
            'average_duration_ms' => $this->queryCount === 0 ? 0.0 : $this->totalDurationMs / $this->queryCount,
            'error_count'       => $this->errorCount,
            'by_connection'     => $this->byConnection,
        ];
    }

    public function reset(): void
    {
        $this->queryCount   = 0;
        $this->totalDurationMs = 0.0;
        $this->errorCount   = 0;
        $this->byConnection = [];
    }
}
