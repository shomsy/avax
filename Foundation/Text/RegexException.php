<?php

declare(strict_types=1);

namespace Avax\Text;

use RuntimeException;

/**
 * Exception for regex operation errors.
 */
final class RegexException extends RuntimeException
{
    /**
     * Create exception from last PCRE error.
     */
    public static function fromLastError(string $pattern) : self
    {
        $code = preg_last_error();

        $message = match ($code) {
            PREG_NO_ERROR              => 'No error',
            PREG_INTERNAL_ERROR        => 'Internal PCRE error',
            PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exhausted',
            PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exhausted',
            PREG_BAD_UTF8_ERROR        => 'Bad UTF-8',
            PREG_BAD_UTF8_OFFSET_ERROR => 'Bad UTF-8 offset',
            PREG_JIT_STACKLIMIT_ERROR  => 'JIT stack limit exhausted',
            default                    => 'Unknown PCRE error',
        };

        return new self(message: $message . ' for pattern: ' . $pattern, code: $code);
    }
}