<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Exceptions;

/**
 * RecoveryException - Session Recovery Errors
 *
 * Specialized exception type for all failures originating from the
 * Recovery subsystem (snapshots, transactions, integrity checks).
 *
 * This allows callers and higher-level components (SessionEngine,
 * admin tooling, observability pipeline) to distinguish recovery
 * errors from generic runtime problems.
 */
final class RecoveryException extends SessionException
{
    public static function transactionAlreadyStarted() : self
    {
        return new self(message: 'Recovery transaction already started.');
    }

    public static function noActiveTransaction(string $operation) : self
    {
        return new self(message: "No active recovery transaction to {$operation}.");
    }

    public static function invalidTransactionState() : self
    {
        return new self(message: 'Recovery transaction state is invalid.');
    }

    public static function integrityCheckFailed(string $name) : self
    {
        return new self(message: "Recovery backup integrity check failed for '{$name}'.");
    }

    public static function transactionFailed(string $reason) : self
    {
        return new self(message: 'Recovery transaction failed: ' . $reason);
    }
}
