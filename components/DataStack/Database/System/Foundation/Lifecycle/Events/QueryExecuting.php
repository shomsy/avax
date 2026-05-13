<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;

/**
 * Event fired before SQL execution.
 *
 * Bindings are redacted by default at the source (QueryOrchestrator).
 * Sensitive keys (password, token, secret, etc.) and token-like values are masked.
 */
final readonly class QueryExecuting
{
    /**
     * @param array<int|string, mixed> $bindings
     */
    public function __construct(
        public string $sql,
        public array $bindings,
        public string $connection,
        public float $startTime,
        public string $phase = QueryLifecyclePhase::Executing->value,
    ) {
    }
}
