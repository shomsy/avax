<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

/**
 * Interface for materialized view operations.
 *
 * Materialized views store the result of a query physically,
 * enabling fast reads at the cost of potential staleness.
 */
interface MaterializedViewInterface
{
    /**
     * Returns the unique name of the materialized view.
     */
    public function name(): string;

    /**
     * Refreshes the materialized view with current data.
     *
     * @return MaterializedViewStats Statistics about the refresh operation
     */
    public function refresh(): MaterializedViewStats;

    /**
     * Reads data from the materialized view.
     *
     * @param  array<string, mixed>  $filters  Optional filters to apply
     * @return array<string, mixed>|list<mixed>
     */
    public function read(array $filters = []): array;

    /**
     * Checks if the materialized view is stale.
     */
    public function isStale(): bool;

    /**
     * Returns the age of the materialized view in seconds.
     */
    public function age(): float;

    /**
     * Returns the timestamp of the last refresh.
     */
    public function lastRefreshedAt(): float;

    /**
     * Returns the staleness threshold in seconds.
     */
    public function stalenessThreshold(): float;
}

/**
 * Statistics about a materialized view refresh operation.
 */
final readonly class MaterializedViewReader
{
    public function __construct(
        public string $viewName,
        public int $rowsAffected = 0,
        public float $durationMs = 0.0,
        public float $refreshedAt = 0.0,
        public bool $success = true,
        public string|null $error = null,
    ) {
    }

    /**
     * Creates a failure stats object.
     */
    public static function failure(string $viewName, string $error): self
    {
        return new self(
            viewName   : $viewName,
            refreshedAt: microtime(true),
            success    : false,
            error      : $error,
        );
    }
}
