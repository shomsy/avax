<?php

declare(strict_types=1);

namespace Gemini\Logging;

use Throwable;

/**
 * Interface GlobalErrorHandlerInterface
 *
 * Defines the contract for global error handling within the application.
 */
interface GlobalErrorHandlerInterface
{
    /**
     * Initializes error handling configuration.
     *
     * @throws \Exception
     */
    public function initialize(): void;

    /**
     * Handles exceptions and logs the error details.
     *
     * @param Throwable $throwable - The exception to handle.
     *
     * @throws \Exception
     */
    public function handle(Throwable $throwable): void;

    /**
     * Logs custom messages for debugging.
     *
     * @param string $message - Custom message to log.
     * @param mixed $context - Additional context for the log entry.
     */
    public function log(string $message, mixed $context = null): void;

    /**
     * Dumps variables during development for debugging purposes.
     *
     * @param mixed $dumpMe - Variable to dump.
     */
    public function dumpIt(mixed $dumpMe): void;
}