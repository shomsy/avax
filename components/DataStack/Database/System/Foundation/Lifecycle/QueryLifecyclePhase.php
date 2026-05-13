<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Query lifecycle phases for database query execution.
 *
 * Query lifecycle is telemetry/observability first.
 * SQL and bindings are redacted by default.
 */
enum QueryLifecyclePhase: string
{
    case Executing = 'executing';
    case Executed = 'executed';
    case Slow = 'slow';
    case Failed = 'failed';
}
