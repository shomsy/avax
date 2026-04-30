<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use RuntimeException;

final class PoolException extends RuntimeException
{
    public static function poolExhausted() : self
    {
        return new self(message: 'Connection pool exhausted');
    }

    public static function invalidConnection() : self
    {
        return new self(message: 'Invalid connection');
    }

    public static function timeout(int $timeoutMs) : self
    {
        return new self(message: sprintf('Connection timeout after %dms', $timeoutMs));
    }
}
