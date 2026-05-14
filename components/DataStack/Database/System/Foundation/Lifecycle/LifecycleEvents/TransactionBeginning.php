<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;

/**
 * Event fired before BEGIN SQL.
 */
final readonly class TransactionBeginning
{
    public function __construct(
        public string $connection,
        public int $nestingLevel,
        public string $transactionId,
        public string $phase = TransactionLifecyclePhase::Beginning->value,
    ) {
    }
}
