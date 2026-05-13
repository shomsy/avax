<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Registration record for a query lifecycle listener.
 *
 * Created by the onQuery() DSL. Query lifecycle is telemetry/observability first.
 * Includes an optional thresholdMs for slow query detection.
 */
final readonly class QueryLifecycleRegistration
{
    public function __construct(
        public QueryLifecyclePhase $phase,
        public string $listener,
        public int $priority = 0,
        public LifecycleSource $source = LifecycleSource::Dsl,
        public LifecycleExecutionMode $mode = LifecycleExecutionMode::Sync,
        public ?int $thresholdMs = null,
    ) {
    }
}
