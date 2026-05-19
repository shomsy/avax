<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Transactions;

enum IsolationLevel: string
{
    case READ_UNCOMMITTED = 'READ UNCOMMITTED';
    case READ_COMMITTED = 'READ COMMITTED';
    case REPEATABLE_READ = 'REPEATABLE READ';
    case SERIALIZABLE = 'SERIALIZABLE';
    case SNAPSHOT = 'SNAPSHOT';

    public function toSql(): string
    {
        return match ($this) {
            self::READ_UNCOMMITTED => 'SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            self::READ_COMMITTED => 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
            self::REPEATABLE_READ => 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ',
            self::SERIALIZABLE => 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            self::SNAPSHOT => 'SET TRANSACTION ISOLATION LEVEL SNAPSHOT',
        };
    }

    public function supports(string $dialect): bool
    {
        return match ($dialect) {
            'mysql' => true,
            'postgresql' => $this !== self::SNAPSHOT,
            'sqlite' => $this === self::READ_COMMITTED || $this === self::SERIALIZABLE,
            'sqlserver' => true,
            default => false,
        };
    }
}
