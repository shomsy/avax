<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;
use Throwable;

/**
 * Event fired after rollback, for cleanup after transaction failure.
 */
final readonly class AfterRollback
{
    public function __construct(
        public string $connection,
        public string $transactionId,
        public ?Throwable $reason = null,
        public string $phase = TransactionLifecyclePhase::AfterRollback->value,
    ) {
    }
}
