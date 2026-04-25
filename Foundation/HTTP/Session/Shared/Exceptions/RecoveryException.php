<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Exceptions;

use RuntimeException;

final class RecoveryException extends RuntimeException
{
    public static function transactionAlreadyStarted() : self
    {
        return new self(message: 'A session recovery transaction is already active.');
    }

    public static function noActiveTransaction(string $operation) : self
    {
        return new self(message: "Cannot {$operation} because no session recovery transaction is active.");
    }

    public static function invalidTransactionState() : self
    {
        return new self(message: 'Session recovery transaction state is invalid.');
    }

    public static function integrityCheckFailed(string $name) : self
    {
        return new self(message: "Session recovery snapshot '{$name}' failed integrity validation.");
    }

    public static function transactionFailed(string $reason) : self
    {
        return new self(message: 'Session recovery transaction failed: ' . $reason);
    }
}
