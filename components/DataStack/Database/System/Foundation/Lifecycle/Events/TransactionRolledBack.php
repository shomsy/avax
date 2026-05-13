<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;
use Throwable;

/**
 * Event fired after ROLLBACK SQL.
 */
final readonly class TransactionRolledBack
{
    public function __construct(
        public string $connection,
        public int $nestingLevel,
        public string $transactionId,
        public ?Throwable $reason = null,
        public string $phase = TransactionLifecyclePhase::RolledBack->value,
    ) {
    }
}
