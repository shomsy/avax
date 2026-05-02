<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\ErrorHandling;

use Avax\Components\Operations\Logging\System\PublicSurface\Logging;
use Avax\Framework\System\Flows\HandleRuntimeFailure\HandleRuntimeFailure;
use Throwable;

/**
 * Global error handler that registers PHP error handling callbacks.
 *
 * Manages the full error handling lifecycle:
 * - set_exception_handler: Catches uncaught exceptions
 * - set_error_handler: Converts PHP errors to throwables
 * - Shutdown handler (via ShutdownErrorHandler) : Catches fatal errors
 *
 * This class is designed to be registered during framework boot.
 */
final class GlobalErrorHandler
{
    private static ?self $instance = null;

    private bool $registered = false;

    /**
     * @var callable|null Previous exception handler to delegate to
     */
    private $previousExceptionHandler;

    /**
     * @var callable|null Previous error handler to delegate to for suppressed errors
     */
    private $previousErrorHandler;

    public function __construct(
        private readonly HandleRuntimeFailure $handleRuntimeFailure,
        private readonly ShutdownErrorHandler $shutdownErrorHandler,
        private readonly Logging $logging,
    ) {}

    /**
     * Get the singleton instance.
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    /**
     * Set the singleton instance.
     */
    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Register all global error handlers.
     *
     * Should be called once during framework boot.
     */
    public function register(): void
    {
        if ($this->registered) {
            $this->logging->warning('GlobalErrorHandler::register called but handlers are already registered');

            return;
        }

        $this->previousExceptionHandler = set_exception_handler($this->handleException(...));
        $this->previousErrorHandler = set_error_handler($this->handleError(...));

        $this->shutdownErrorHandler->register();

        $this->registered = true;

        $this->logging->info('Global error handlers registered');
    }

    /**
     * Unregister all global error handlers and restore previous handlers.
     */
    public function unregister(): void
    {
        if (! $this->registered) {
            return;
        }

        restore_exception_handler();
        restore_error_handler();

        $this->shutdownErrorHandler->unregister();

        $this->registered = false;

        $this->logging->info('Global error handlers unregistered');
    }

    /**
     * Get the previous exception handler that was replaced.
     *
     * @return callable|null
     */
    public function getPreviousExceptionHandler()
    {
        return $this->previousExceptionHandler;
    }

    /**
     * Check if handlers are currently registered.
     */
    public function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * Handle uncaught exceptions.
     */
    public function handleException(Throwable $throwable) : never
    {
        $this->handleRuntimeFailure->handleException($throwable);
    }

    /**
     * Handle PHP errors by converting them to throwables.
     *
     * @return false Return false to let PHP's internal error handler run for suppressed errors
     */
    public function handleError(int $severity, string $message, string $file, int $line): false
    {
        // If error reporting is suppressed (e.g., with @), delegate to previous handler
        if ((error_reporting() & $severity) === 0) {
            if ($this->previousErrorHandler !== null) {
                return ($this->previousErrorHandler)($severity, $message, $file, $line);
            }

            return false;
        }

        try {
            $this->handleRuntimeFailure->handle($severity, $message, $file, $line);
        } catch (Throwable $throwable) {
            // If the handler itself fails, log and exit
            $this->logging->critical(
                'Error handler failed: ' . $throwable->getMessage(),
                [
                    'original_error' => [
                        'severity' => $severity,
                        'message' => $message,
                        'file'    => $file,
                        'line'    => $line,
                    ],
                    'handler_exception' => $throwable->getMessage(),
                ],
            );

            exit(1);
        }

        // Unreachable - handle() calls exit(1) but return type requires false
        // @phpstan-ignore deadCode.unreachable
        return false;
    }

    /**
     * Handle a fatal error during shutdown.
     */
    public function handleShutdown(): void
    {
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

        $this->handleRuntimeFailure->handleFatalError($error);
    }
}
