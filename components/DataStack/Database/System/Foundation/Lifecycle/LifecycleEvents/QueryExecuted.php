<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;

/**
 * Event fired after SQL execution success.
 *
 * Bindings are redacted by default at the source (QueryOrchestrator).
 * Sensitive keys (password, token, secret, etc.) and token-like values are masked.
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
