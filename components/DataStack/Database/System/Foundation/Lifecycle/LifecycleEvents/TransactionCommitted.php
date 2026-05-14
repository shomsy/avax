<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;

/**
 * Event fired after COMMIT SQL success.
 */
final readonly class TransactionCommitted
{
    public function __construct(
        public string $connection,
        public int $nestingLevel,
        public string $transactionId,
        public float $durationMs,
        public string $phase = TransactionLifecyclePhase::Committed->value,
    ) {
    }
}
