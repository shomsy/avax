<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

use Closure;
use RuntimeException;
use Throwable;

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
     * @param array<string, mixed> $filters Optional filters to apply
     *
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
        public ?string $error = null,
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

/**
 * Simple implementation of a materialized view for query optimization.
 *
 * Stores query results in memory and tracks staleness.
 * In production, this would typically be backed by a database table
 * or a cache store.
 *
 * @template T of array
 *
 * @implements MaterializedViewInterface
 */
final class MaterializedView implements MaterializedViewInterface
{
    /**
     * @var T|null Cached data
     */
    private ?array $data = null;

    /**
     * @var float|null Timestamp of last refresh
     */
    private ?float $lastRefreshedAt = null;

    /**
     * @var int Number of rows in the last refresh
     */
    private int $rowCount = 0;

    /**
     * @param Closure() : T $query              Closure that produces the view data
     * @param float         $stalenessThreshold Seconds before view is considered stale
     */
    public function __construct(
        /**
         * @var string Unique view name
         */
        private readonly string  $name,
        private readonly Closure $query,
        private readonly float   $stalenessThreshold = 3600.0
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function refresh(): MaterializedViewStats
    {
        $startTime = microtime(true);

        try {
            $data                  = ($this->query)();
            $this->data            = $data;
            $this->lastRefreshedAt = microtime(true);
            $this->rowCount        = is_array($data) ? count($data) : 0;

            $durationMs = (microtime(true) - $startTime) * 1000;

            return new MaterializedViewStats(
                viewName    : $this->name,
                rowsAffected: $this->rowCount,
                durationMs  : $durationMs,
                refreshedAt : $this->lastRefreshedAt,
            );
        } catch (Throwable $throwable) {
            return MaterializedViewStats::failure($this->name, $throwable->getMessage());
        }
    }

    public function read(array $filters = []): array
    {
        if ($this->data === null) {
            throw new RuntimeException(
                sprintf("Materialized view '%s' has not been refreshed yet", $this->name),
            );
        }

        if ($filters === []) {
            return $this->data;
        }

        return array_values(array_filter(
            $this->data,
                                static fn (array $row) : bool => array_all($filters, fn ($value, $key) : bool => isset($row[$key]) && $row[$key] === $value),
        ));
    }

    public function lastRefreshedAt(): float
    {
        return $this->lastRefreshedAt ?? 0.0;
    }

    public function stalenessThreshold(): float
    {
        return $this->stalenessThreshold;
    }

    /**
     * Returns the number of rows in the cached data.
     */
    public function rowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Checks if the view has been refreshed at least once.
     */
    public function hasData(): bool
    {
        return $this->data !== null;
    }

    /**
     * Clears the cached data without triggering a refresh.
     */
    public function clear(): void
    {
        $this->data            = null;
        $this->lastRefreshedAt = null;
        $this->rowCount        = 0;
    }

    /**
     * Returns the cached data directly (for inspection).
     */
    public function data(): ?array
    {
        return $this->data;
    }

    /**
     * Returns a summary of the view state.
     */
    public function summary(): string
    {
        if ($this->data === null) {
            return sprintf("View '%s': not refreshed", $this->name);
        }

        $age = $this->age();
        $staleness = $this->isStale() ? 'STALE' : 'FRESH';

        return sprintf(
            "View '%s': %d rows, age: %.1fs [%s]",
            $this->name,
            $this->rowCount,
            $age,
            $staleness,
        );
    }

    public function age(): float
    {
        if ($this->lastRefreshedAt === null) {
            return PHP_FLOAT_MAX;
        }

        return microtime(true) - $this->lastRefreshedAt;
    }

    public function isStale(): bool
    {
        if ($this->lastRefreshedAt === null) {
            return true;
        }

        return (microtime(true) - $this->lastRefreshedAt) >= $this->stalenessThreshold;
    }
}

/**
 * Registry for managing multiple materialized views.
 */
final class MaterializedViewRegistry
{
    /**
     * @var array<string, MaterializedView>
     */
    private array $views = [];

    /**
     * Registers a materialized view.
     */
    public function register(MaterializedView $materializedView) : void
    {
        $this->views[$materializedView->name()] = $materializedView;
    }

    /**
     * Refreshes all registered views.
     *
     * @return array<string, MaterializedViewStats>
     */
    public function refreshAll(): array
    {
        $results = [];

        foreach ($this->views as $name => $view) {
            $results[$name] = $view->refresh();
        }

        return $results;
    }

    /**
     * Refreshes a specific view.
     */
    public function refresh(string $name): MaterializedViewStats
    {
        return $this->get($name)->refresh();
    }

    /**
     * Returns a registered view by name.
     *
     * @throws RuntimeException If the view is not found
     */
    public function get(string $name): MaterializedView
    {
        if (! isset($this->views[$name])) {
            throw new RuntimeException(sprintf("Materialized view '%s' is not registered", $name));
        }

        return $this->views[$name];
    }

    /**
     * Returns all stale views.
     *
     * @return list<MaterializedView>
     */
    public function staleViews(): array
    {
        return array_values(array_filter(
            $this->views,
                                static fn (MaterializedView $materializedView) : bool => $materializedView->isStale(),
        ));
    }

    /**
     * Returns all registered view names.
     *
     * @return list<string>
     */
    public function viewNames(): array
    {
        return array_keys($this->views);
    }

    /**
     * Returns whether a view is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->views[$name]);
    }

    /**
     * Removes a registered view.
     */
    public function remove(string $name): void
    {
        unset($this->views[$name]);
    }

    /**
     * Returns the count of registered views.
     */
    public function count(): int
    {
        return count($this->views);
    }

    /**
     * Clears all registered views.
     */
    public function clear(): void
    {
        $this->views = [];
    }
}
