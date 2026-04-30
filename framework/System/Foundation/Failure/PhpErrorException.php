<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Failure;

use Throwable;

/**
 * Custom exception for PHP errors that are fatal in nature.
 *
 * Used to distinguish fatal PHP errors (E_ERROR, E_PARSE, etc.) from
 * standard ErrorException instances.
 */
final class PhpErrorException extends FrameworkFailure
{
    public function __construct(
        string                  $message,
        private readonly int    $severity,
        private readonly string $errorName,
        private readonly string $errorFile = '',
        private readonly int    $errorLine = 0,
        ?Throwable              $previous = null,
    )
    {
        parent::__construct($message, 0, $previous);
    }

    public function getSeverity() : int
    {
        return $this->severity;
    }

    public function getErrorName() : string
    {
        return $this->errorName;
    }

    public function getErrorFile() : string
    {
        return $this->errorFile;
    }

    public function getErrorLine() : int
    {
        return $this->errorLine;
    }

    public function isFatal() : bool
    {
        return in_array($this->severity, [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
        ],              true);
    }
}
