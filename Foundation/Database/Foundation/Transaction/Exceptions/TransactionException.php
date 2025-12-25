<?php

declare(strict_types=1);

namespace Avax\Database\Transaction\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Override;
use Throwable;

/**
 * Exception thrown when a transaction-level operation fails.
 */
final class TransactionException extends DatabaseException
{
    /**
     * Constructor capturing the failure context and nesting level.
     *
     * -- intent: store technical details about the failed unit of work.
     *
     * @param string         $message      Technical description of the transaction failure
     * @param int            $nestingLevel The transaction depth when the failure occurred
     * @param Throwable|null $previous     Underlying driver or callback error
     */
    #[Override]
    public function __construct(
        string               $message,
        private readonly int $nestingLevel,
        Throwable|null       $previous = null
    )
    {
        parent::__construct(
            message : "Transaction failed (Level {$nestingLevel}): {$message}",
            code    : 0,
            previous: $previous
        );
    }

    /**
     * Retrieve the transaction nesting depth at the time of failure.
     *
     * -- intent: help diagnose complex nested transaction issues.
     *
     * @return int
     */
    public function getNestingLevel() : int
    {
        return $this->nestingLevel;
    }
}
