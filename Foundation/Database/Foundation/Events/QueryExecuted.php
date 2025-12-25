<?php

declare(strict_types=1);

namespace Avax\Database\Events;

use Override;

/**
 * Event signaled after a database query has been executed.
 *
 * -- intent: provide telemetry data for query performance monitoring and debugging.
 */
final readonly class QueryExecuted extends Event
{
    #[Override]
    public function __construct(
        public string $sql,
        public array  $bindings,
        public float  $timeMs,
        public string $connectionName
    )
    {
        parent::__construct();
    }
}
