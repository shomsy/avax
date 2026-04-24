<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

/**
 * IsolationLevel - visible transaction isolation choices for DataLayer commit policies.
 */
enum IsolationLevel: string
{
    case ReadUncommitted = 'read_uncommitted';
    case ReadCommitted   = 'read_committed';
    case RepeatableRead  = 'repeatable_read';
    case Serializable    = 'serializable';
}
