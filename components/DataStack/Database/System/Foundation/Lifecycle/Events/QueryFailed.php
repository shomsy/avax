<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;
use Throwable;

/**
 * Event fired after SQL exception.
 *
 * Exception still bubbles — this event reports, it does not catch.
 * SQL and bindings are redacted by default for security.
 */
final readonly class QueryFailed
{
    /**
     * @param array<int|string, mixed> $bindings
     */
    public function __construct(
        public string $sql,
        public array $bindings,
        public string $connection,
        public Throwable $exception,
        public float $durationMs,
        public string $phase = QueryLifecyclePhase::Failed->value,
    ) {
    }
}
