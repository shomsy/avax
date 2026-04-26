<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Transactions;

use PDOException;
use Throwable;

final class DeadlockDetector
{
    private const SQLSTATE_DEADLOCKS = ['40001', '40P01'];

    public function isDeadlock(Throwable $exception) : bool
    {
        if ($exception instanceof PDOException && in_array(needle: (string) $exception->getCode(), haystack: self::SQLSTATE_DEADLOCKS, strict: true)) {
            return true;
        }

        $message = strtolower(string: $exception->getMessage());

        return str_contains(haystack: $message, needle: 'deadlock')
            || str_contains(haystack: $message, needle: 'serialization failure')
            || str_contains(haystack: $message, needle: 'lock wait timeout');
    }
}
