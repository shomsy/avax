<?php

declare(strict_types=1);

/**
 * Logging shortcuts for global access.
 */

use Psr\Log\LoggerInterface;

if (! function_exists('logger')) {
    /**
     * Log a message or get the Logger instance.
     */
    function logger(?string $message = null, ?array $context = null, string $level = 'info') : ?LoggerInterface
    {
        $context ??= [];
        $logger = app(LoggerInterface::class);

        if ($message === null) {
            return $logger;
        }

        $logger->log($level, $message, $context);

        return null;
    }
}
