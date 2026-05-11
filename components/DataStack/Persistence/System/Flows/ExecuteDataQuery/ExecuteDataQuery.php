<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\ExecuteDataQuery;

use Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\QueryFingerprint;
use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQueryPlan;
use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\QueryResult;
use function count;

/**
 * ExecuteDataQuery flow.
 *
 * Executes a query plan against a database connection.
 * Returns QueryResult with timing information.
 */
final class ExecuteDataQuery
{
    /**
     * @var callable|null
     */
    private $executor;

    /**
     * @param  callable(DataQueryPlan) : array<array<string, mixed>>|null  $executor
     */
    public function __construct(callable|null $executor = null)
    {
        $this->executor = $executor;
    }

    /**
     * Executes the query plan and returns the result.
     */
    public function execute(DataQueryPlan $dataQueryPlan): QueryResult
    {
        $startTime = $this->currentTimeMs();

        $rows = $this->executeQuery($dataQueryPlan);

        $endTime = $this->currentTimeMs();
        $tookMs = $endTime - $startTime;

        $queryFingerprint = QueryFingerprint::fromQuery($dataQueryPlan->sql ?? '');

        return new QueryResult(
            rows       : $rows,
            count      : count($rows),
            tookMs     : $tookMs,
            fingerprint: $queryFingerprint,
        );
    }

    /**
     * Gets the current time in milliseconds.
     */
    private function currentTimeMs(): float
    {
        return microtime(true) * 1000;
    }

    /**
     * Executes the actual query using the configured executor.
     *
     * @return array<array<string, mixed>>
     */
    private function executeQuery(DataQueryPlan $dataQueryPlan): array
    {
        if ($this->executor !== null) {
            return ($this->executor)($dataQueryPlan);
        }

        // Default implementation: return empty result set
        // In production, this would execute against a real database
        return [];
    }
}
