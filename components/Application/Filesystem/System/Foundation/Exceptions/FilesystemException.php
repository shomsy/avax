<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown by filesystem operations.
 */
class FilesystemException extends Exception
{
    public function __construct(string $message, string $path = '', int $code = 0, ?Throwable $throwable = null)
    {
        $fullMessage = $message;
        if ($path !== '' && $path !== '0') {
            $fullMessage .= sprintf(' (path: %s)', $path);
        }

        parent::__construct(message: $fullMessage, code: $code, previous: $throwable);
    }
}
