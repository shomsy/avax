<?php
declare(strict_types=1);

use Psr\Log\LoggerInterface;

if (!function_exists('logger')) {
    function logger(?string $message = null, ?array $context = null, string $level = 'info'): ?LoggerInterface
    {
        // Assumes app() helper exists
        $logger = app(LoggerInterface::class);

        if ($message === null) {
            return $logger;
        }

        $logger->log($level, $message, $context ?? []);

        return null;
    }
}
