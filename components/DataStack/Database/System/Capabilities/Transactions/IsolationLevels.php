<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

final class IsolationLevels
{
    public const READ_UNCOMMITTED = IsolationLevel::READ_UNCOMMITTED;

    public const READ_COMMITTED = IsolationLevel::READ_COMMITTED;

    public const REPEATABLE_READ = IsolationLevel::REPEATABLE_READ;

    public const SERIALIZABLE = IsolationLevel::SERIALIZABLE;

    public const SNAPSHOT = IsolationLevel::SNAPSHOT;
}
