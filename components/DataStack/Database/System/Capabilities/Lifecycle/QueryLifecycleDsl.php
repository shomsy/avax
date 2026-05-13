<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Lifecycle;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleSource;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecycleRegistration;

/**
 * Fluent DSL for registering query lifecycle listeners.
 *
 * Usage:
 *   onQuery()
 *       ->executed(RecordQueryTelemetry::class)
 *       ->slow(ReportSlowQuery::class, thresholdMs: 100)
 *       ->failed(RecordFailedQuery::class);
 *
 * Query lifecycle is telemetry/observability first.
 * SQL and bindings are redacted by default.
 * This is a declaration API only.
 */
final class QueryLifecycleDsl
{
    private static CompiledDatabaseLifecycleRegistry|null $registry = null;

    public function __construct()
    {
    }

    /**
     * Register a listener for the executing phase (before query execution).
     */
    public function executing(string $listener, int $priority = 0): self
    {
        $this->register(QueryLifecyclePhase::Executing, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the executed phase (after query success).
     */
    public function executed(string $listener, int $priority = 0): self
    {
        $this->register(QueryLifecyclePhase::Executed, $listener, $priority);

        return $this;
    }

    /**
     * Register a listener for the slow phase (when query exceeds threshold).
     */
    public function slow(string $listener, int $thresholdMs = 100, int $priority = 0): self
    {
        $this->register(QueryLifecyclePhase::Slow, $listener, $priority, $thresholdMs);

        return $this;
    }

    /**
     * Register a listener for the failed phase (after query exception).
     */
    public function failed(string $listener, int $priority = 0): self
    {
        $this->register(QueryLifecyclePhase::Failed, $listener, $priority);

        return $this;
    }

    private function register(QueryLifecyclePhase $phase, string $listener, int $priority, ?int $thresholdMs = null): void
    {
        $registration = new QueryLifecycleRegistration(
            phase: $phase,
            listener: $listener,
            priority: $priority,
            source: LifecycleSource::Dsl,
            thresholdMs: $thresholdMs,
        );

        $this->registry()->registerQuery($registration);
    }

    private function registry(): CompiledDatabaseLifecycleRegistry
    {
        if (self::$registry === null) {
            self::$registry = new CompiledDatabaseLifecycleRegistry();
        }

        return self::$registry;
    }

    /**
     * Set the shared registry instance (used by boot-time compilation).
     */
    public static function setRegistry(CompiledDatabaseLifecycleRegistry $registry): void
    {
        self::$registry = $registry;
    }

    /**
     * Get the current registry instance.
     */
    public static function getRegistry(): CompiledDatabaseLifecycleRegistry
    {
        if (self::$registry === null) {
            self::$registry = new CompiledDatabaseLifecycleRegistry();
        }

        return self::$registry;
    }

    /**
     * Reset the registry (used for testing).
     */
    public static function reset(): void
    {
        self::$registry = null;
    }
}
