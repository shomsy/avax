<?php

declare(strict_types=1);

namespace Avax\Database\Transaction\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Throwable;

/**
 * technical exception triggered when a database transaction-level operation fails.
 *
 * -- intent:
 * Provides specific technical context for failures occurring during the
 * coordination of atomic persistence sequences, capturing both the failure
 * description and the transaction nesting depth to facilitate precise diagnostic analysis.
 *
 * -- invariants:
 * - Instances must be strictly immutable to maintain error condition integrity.
 * - Always includes the technical transaction depth (nesting level) at the moment of failure.
 * - Supports chaining via the native technical 'previous' exception mechanism.
 *
 * -- boundaries:
 * - Does NOT perform transaction recovery; strictly used for classification and reporting.
 * - Inherits from the base DatabaseException contract.
 */
final class TransactionException extends DatabaseException
{
    /**
     * @param string         $message      The detailed technical description of the transaction coordination failure.
     * @param int            $nestingLevel The technical transaction depth (0-based or 1-based) when the failure was
     *                                     intercepted.
     * @param Throwable|null $previous     The underlying technical driver or unit-of-work exception.
     */
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
     * Retrieve the technical transaction nesting depth recorded at the moment of failure.
     *
     * -- intent:
     * Enables developers to diagnose complex nested transaction issues,
     * identifying if the failure occurred at the root or within a saved point.
     *
     * @return int The technical integer representation of the transaction depth.
     */
    public function getNestingLevel() : int
    {
        return $this->nestingLevel;
    }
}
