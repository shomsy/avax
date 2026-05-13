<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\TransactionLifecyclePhase;

/**
 * Event fired after successful commit, safe for external side effects.
 *
 * This is the safe place for email, HTTP, queue, outbox publication.
 * Must NOT run if transaction fails or rolls back.
 */
final readonly class AfterCommit
{
    public function __construct(
        public string $connection,
        public string $transactionId,
        public string $phase = TransactionLifecyclePhase::AfterCommit->value,
    ) {
    }
}
