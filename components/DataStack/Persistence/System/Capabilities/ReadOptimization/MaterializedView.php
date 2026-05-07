<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

use Closure;
use RuntimeException;
use Throwable;

/**
 * @template T of array
 */
final class MaterializedView implements MaterializedViewInterface
{
    /**
     * @var T|null Cached data
     */
    private ?array $data = null;

    private ?float $lastRefreshedAt = null;

    private int $rowCount = 0;

    /**
     * @param Closure() : T $query              Closure that produces the view data
     * @param float         $stalenessThreshold Seconds before view is considered stale
     */
    public function __construct(
        /**
         * @var string Unique view name
         */
        private readonly string $name,
        private readonly Closure $query,
        private readonly float $stalenessThreshold = 3600.0
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function refresh(): MaterializedViewStats
    {
        $startTime = microtime(true);

        try {
            $data = ($this->query)();
            $this->data = $data;
            $this->lastRefreshedAt = microtime(true);
            $this->rowCount = count($data);

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
            static fn (array $row): bool => array_all($filters, fn ($value, $key): bool => isset($row[$key]) && $row[$key] === $value),
        ));
    }

    public function lastRefreshedAt(): float
    {
        return $this->lastRefreshedAt ?? 0.0;
    }

    public function stalenessThreshold() : float
    {
        return $this->stalenessThreshold;
    }

    public function rowCount() : int
    {
        return $this->rowCount;
    }

    public function hasData() : bool
    {
        return $this->data !== null;
    }

    public function clear() : void
    {
        $this->data            = null;
        $this->lastRefreshedAt = null;
        $this->rowCount        = 0;
    }

    /**
     * @return T|null
     */
    public function data() : ?array
    {
        return $this->data;
    }

    public function summary() : string
    {
        if ($this->data === null) {
            return sprintf("View '%s': not refreshed", $this->name);
        }

        $age       = $this->age();
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

    public function isStale() : bool
    {
        if ($this->lastRefreshedAt === null) {
            return true;
        }

        return (microtime(true) - $this->lastRefreshedAt) >= $this->stalenessThreshold;
    }
}
