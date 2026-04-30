<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleRuntimeFailure;

use Avax\Framework\System\Foundation\Failure\PhpErrorException;
use ErrorException;
use Throwable;

/**
 * Converts PHP errors to throwable exceptions.
 *
 * Maps PHP error constants to appropriate exception types and severity levels.
 */
final readonly class ConvertPhpErrorToThrowable
{
    /**
     * Map of PHP error constants to their human-readable names.
     */
    private const ERROR_NAMES
        = [
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        ];

    /**
     * Error types that should always be converted to exceptions.
     */
    private const FATAL_ERRORS
        = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
            E_RECOVERABLE_ERROR,
        ];

    /**
     * Convert an error array (from error_get_last()) to a throwable exception.
     *
     * @param array{type: int, message: string, file: string, line: int} $error
     */
    public function convertFromErrorArray(array $error) : Throwable
    {
        return $this->convert(
            severity: $error['type'],
            message : $error['message'],
            file    : $error['file'],
            line    : $error['line'],
        );
    }

    /**
     * Convert a PHP error to a throwable exception.
     *
     * @throws ErrorException
     * @throws PhpErrorException
     */
    public function convert(int $severity, string $message, string $file, int $line) : Throwable
    {
        $errorName = self::ERROR_NAMES[$severity] ?? "E_UNKNOWN({$severity})";

        if (in_array($severity, self::FATAL_ERRORS, true)) {
            throw new PhpErrorException(
                message  : "[{$errorName}] {$message}",
                severity : $severity,
                errorName: $errorName,
                errorFile: $file,
                errorLine: $line,
            );
        }

        throw new ErrorException(
            message : "[{$errorName}] {$message}",
            code    : 0,
            severity: $severity,
            filename: $file,
            line    : $line,
        );
    }
}
