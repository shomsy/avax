<?php

declare(strict_types=1);

namespace Avax\Contracts;

use Exception;
use Throwable;

/**
 * Exception thrown by filesystem operations.
 */
class FilesystemException extends Exception
{
    public function __construct(string $message, string $path = '', int $code = 0, Throwable|null $previous = null)
    {
        $fullMessage = $message;
        if (! empty($path)) {
            $fullMessage .= " (path: {$path})";
        }

        parent::__construct(message: $fullMessage, code: $code, previous: $previous);
    }
}
