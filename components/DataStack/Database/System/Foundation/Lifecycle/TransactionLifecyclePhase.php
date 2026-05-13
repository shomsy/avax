<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Transaction lifecycle phases for database transaction operations.
 */
enum TransactionLifecyclePhase: string
{
    case Beginning = 'beginning';
    case Committed = 'committed';
    case AfterCommit = 'afterCommit';
    case RolledBack = 'rolledBack';
    case AfterRollback = 'afterRollback';
    case Failed = 'failed';
}
