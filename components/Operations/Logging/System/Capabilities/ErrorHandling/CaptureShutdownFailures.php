<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\ErrorHandling;

use Avax\Components\Operations\Logging\System\PublicSurface\Logging;
use Avax\Framework\System\Flows\HandleRuntimeFailure\HandleRuntimeFailure;
use Throwable;

final class CaptureShutdownFailures
{
    private static ?self $instance = null;

    private bool $registered = false;

    public function __construct(
        private readonly HandleRuntimeFailure $handleRuntimeFailure,
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
            return;
        }

        register_shutdown_function($this->handleShutdown(...));

        $this->registered = true;

        $this->logging->debug('Shutdown error handler registered');
    }

    public function unregister(): void
    {
        $this->registered = false;
    }

    public function isRegistered(): bool
    {
        return $this->registered;
    }

    public function handleShutdown(): void
    {
        if (! $this->registered) {
            return;
        }

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

        $this->logging->critical(
            'Fatal error during shutdown',
            [
                'error_type' => $this->getErrorTypeName($error['type']),
                'error_message' => $error['message'],
                'error_file' => $error['file'],
                'error_line' => $error['line'],
            ],
        );

        try {
            $this->handleRuntimeFailure->handleFatalError($error);
        } catch (Throwable $throwable) {
            $this->logging->emergency(
                'Shutdown error handler failed: '.$throwable->getMessage(),
                ['original_fatal_error' => $error],
            );
        }
    }

    private function getErrorTypeName(int $type): string
    {
        return match ($type) {
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            default => sprintf('E_UNKNOWN(%s)', $type),
        };
    }
}
