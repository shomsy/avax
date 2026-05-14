<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\ErrorHandling;

use Avax\Components\Operations\Logging\System\PublicSurface\Logging;
use Avax\Framework\System\Flows\HandleRuntimeFailure\HandleRuntimeFailure;
use Throwable;

final class CaptureUnhandledErrors
{
    private static ?self $instance = null;

    private bool $registered = false;

    /** @var callable|null */
    private $previousExceptionHandler;

    /** @var callable|null */
    private $previousErrorHandler;

    public function __construct(
        private readonly HandleRuntimeFailure $handleRuntimeFailure,
        private readonly CaptureShutdownFailures $shutdownErrorHandler,
        private readonly Logging $logging,
    ) {
    }

    public static function getInstance() : self|null
    {
        return self::$instance;
    }

    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    public function register(): void
    {
        if ($this->registered) {
            $this->logging->warning('CaptureUnhandledErrors::register called but handlers are already registered');

            return;
        }

        $this->previousExceptionHandler = set_exception_handler($this->handleException(...));
        $this->previousErrorHandler = set_error_handler($this->handleError(...));

        $this->shutdownErrorHandler->register();

        $this->registered = true;

        $this->logging->info('Global error handlers registered');
    }

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

    public function getPreviousExceptionHandler()
    {
        return $this->previousExceptionHandler;
    }

    public function isRegistered(): bool
    {
        return $this->registered;
    }

    public function handleException(Throwable $throwable): never
    {
        $this->handleRuntimeFailure->handleException($throwable);
    }

    public function handleError(int $severity, string $message, string $file, int $line): false
    {
        if ((error_reporting() & $severity) === 0) {
            if ($this->previousErrorHandler !== null) {
                return ($this->previousErrorHandler)($severity, $message, $file, $line);
            }

            return false;
        }

        try {
            $this->handleRuntimeFailure->handle($severity, $message, $file, $line);
        } catch (Throwable $throwable) {
            $this->logging->critical(
                'Error handler failed: '.$throwable->getMessage(),
                [
                    'original_error' => [
                        'severity' => $severity,
                        'message' => $message,
                        'file' => $file,
                        'line' => $line,
                    ],
                    'handler_exception' => $throwable->getMessage(),
                ],
            );

            exit(1);
        }

        // @phpstan-ignore deadCode.unreachable
        return false;
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

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
