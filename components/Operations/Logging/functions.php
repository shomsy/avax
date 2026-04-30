<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

if (! function_exists(function: 'logger')) {
    /**
     * Log a message or get the Logger instance.
     *
     * @param string|null $message
     * @param array|null $context
     * @param string     $level
     *
     * @return LoggerInterface|null
     */
    function logger(string|null $message = null, array|null $context = null, string $level = 'info') : LoggerInterface|null
    {
        $context ??= [];
        $logger = app(abstract: LoggerInterface::class);

        if ($message === null) {
            return $logger;
        }

        $logger->log(level: $level, message: $message, context: $context);

        return null;
    }
}
