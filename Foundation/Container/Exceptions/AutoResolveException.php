<?php

declare(strict_types=1);

namespace Avax\Container\Exceptions;

use Throwable;

/**
 * Thrown when automatic resolution of a class or dependency fails.
 */
class AutoResolveException extends \RuntimeException
{
    #[\Override]
    public function __construct(string $className, Throwable|null $previous = null)
    {
        $detailedMessage = $this->generateDetailedMessage(className: $className, previous: $previous);

        parent::__construct(
            message: $detailedMessage,
            code: 0,
            previous: $previous
        );
    }

    /**
     * Generates a detailed error message with file, line, stack trace, and previous exception details.
     *
     * @param string $className The name of the class that failed to resolve.
     * @param Throwable|null $previous The previous exception, if any.
     * @return string The detailed error message.
     */
    private function generateDetailedMessage(string $className, Throwable|null $previous): string
    {
        $message = sprintf("Failed to automatically resolve the class '%s'. Check the class dependencies.", $className);

        if ($previous instanceof \Throwable) {
            $file = $previous->getFile();
            $line = $previous->getLine();
            $prevMessage = $previous->getMessage();
            $stackTrace = $previous->getTraceAsString();

            $message .= PHP_EOL
                        . "Previous exception details:" . PHP_EOL
                        . ('  File: ' . $file) . PHP_EOL
                        . ('  Line: ' . $line) . PHP_EOL
                        . sprintf("  Message: '%s'", $prevMessage) . PHP_EOL
                        . "  Stack trace:" . PHP_EOL
                        . $stackTrace;
        }

        return $message;
    }
}
