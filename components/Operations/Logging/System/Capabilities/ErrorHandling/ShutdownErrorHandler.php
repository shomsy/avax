<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\ErrorHandling;

use Avax\Components\Operations\Logging\System\PublicSurface\Logging;
use Avax\Framework\System\Flows\HandleRuntimeFailure\HandleRuntimeFailure;
use Throwable;

/**
 * Shutdown error handler that catches fatal errors via register_shutdown_function.
 *
 * Fatal errors (E_ERROR, E_PARSE, etc.) cannot be caught by set_error_handler.
 * This handler uses register_shutdown_function to check error_get_last() after
 * script termination and report any fatal errors that occurred.
 */
final class ShutdownErrorHandler
{
    private static self|null $instance = null;

    private bool $registered = false;

    public function __construct(
        private readonly HandleRuntimeFailure $handleRuntimeFailure,
        private readonly Logging $logger,
    ) {}

    /**
     * Get the singleton instance.
     */
    public static function getInstance() : ?self
    {
        return self::$instance;
    }

    /**
     * Set the singleton instance.
     */
    public static function setInstance(self $instance) : void
    {
        self::$instance = $instance;
    }

    /**
     * Register the shutdown function.
     */
    public function register() : void
    {
        if ($this->registered) {
            return;
        }

        register_shutdown_function($this->handleShutdown(...));

        $this->registered = true;

        $this->logger->debug('Shutdown error handler registered');
    }

    /**
     * Unregister is not directly possible for shutdown functions,
     * but we can mark it as unregistered to prevent processing.
     */
    public function unregister() : void
    {
        $this->registered = false;
    }

    /**
     * Check if the shutdown handler is registered.
     */
    public function isRegistered() : bool
    {
        return $this->registered;
    }

    /**
     * Handle shutdown event - check for fatal errors.
     */
    public function handleShutdown() : void
    {
        if (! $this->registered) {
            return;
        }

        $error = error_get_last();

        if ($error === null) {
            return;
        }

        // Only handle fatal error types
        $fatalTypes = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
        ];

        if (! in_array($error['type'], $fatalTypes, true)) {
            return;
        }

        $this->logger->critical(
            'Fatal error during shutdown',
            [
                'error_type'    => $this->getErrorTypeName($error['type']),
                'error_message' => $error['message'],
                'error_file'    => $error['file'],
                'error_line'    => $error['line'],
            ],
        );

        // Attempt to render a fatal error response
        // Note: At shutdown, output may have already been partially sent
        try {
            $this->handleRuntimeFailure->handleFatalError($error);
        } catch (Throwable $e) {
            // If even the handler fails, ensure we log the original error
            $this->logger->emergency(
                'Shutdown error handler failed: ' . $e->getMessage(),
                ['original_fatal_error' => $error],
            );
        }
    }

    /**
     * Get the human-readable name for a PHP error type.
     */
    private function getErrorTypeName(int $type) : string
    {
        return match ($type) {
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
            default             => "E_UNKNOWN({$type})",
        };
    }
}
