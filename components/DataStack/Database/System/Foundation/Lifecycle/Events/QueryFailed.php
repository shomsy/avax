<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;
use Throwable;

/**
 * Event fired after SQL exception.
 *
 * Exception still bubbles — this event reports, it does not catch.
 * Bindings are redacted by default at the source (QueryOrchestrator).
 * Sensitive keys (password, token, secret, etc.) and token-like values are masked.
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
