<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Registration record for a transaction lifecycle listener.
 *
 * Created by the onTransaction() DSL.
 * Supports afterCommit and afterRollback safety semantics.
 */
final readonly class TransactionLifecycleRegistration
{
    public function __construct(
        public TransactionLifecyclePhase $phase,
        public string $listener,
        public int $priority = 0,
        public LifecycleSource $source = LifecycleSource::Dsl,
        public LifecycleExecutionMode $mode = LifecycleExecutionMode::Sync,
    ) {
    }
}
