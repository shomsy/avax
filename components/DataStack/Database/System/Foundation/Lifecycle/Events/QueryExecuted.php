<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;

/**
 * Event fired after SQL execution success.
 *
 * SQL and bindings are redacted by default for security.
 */
final readonly class QueryExecuted
{
    /**
     * @param array<int|string, mixed> $bindings
     */
    public function __construct(
        public string $sql,
        public array $bindings,
        public string $connection,
        public float $durationMs,
        public int $rowCount,
        public string $phase = QueryLifecyclePhase::Executed->value,
    ) {
    }
}
