<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Kernel;

use ErrorException;
use RuntimeException;

/**
 * Boots the HTTP kernel runtime environment.
 *
 * Responsibilities:
 * - Configure error and exception handlers
 * - Set timezone defaults
 * - Validate required PHP extensions
 * - Prepare the runtime environment for request handling
 */
final class BootHttpKernel
{
    private bool $booted = false;

    /** @var list<callable> */
    private array $bootCallbacks = [];

    /**
     * Register a callback to run during boot.
     */
    public function onBoot(callable $callback) : self
    {
        $this->bootCallbacks[] = $callback;

        return $this;
    }

    /**
     * Boot the kernel environment.
     *
     * Idempotent - calling multiple times has no additional effect.
     */
    public function boot() : void
    {
        if ($this->booted) {
            return;
        }

        $this->configureErrorHandling();
        $this->validateEnvironment();
        $this->runBootCallbacks();

        $this->booted = true;
    }

    /**
     * Configure PHP error and exception handlers.
     */
    private function configureErrorHandling() : void
    {
        // Convert all errors to ErrorExceptions for consistent handling
        set_error_handler(static function (int $severity, string $message, string $file, int $line) : bool {
            if ((error_reporting() & $severity) === 0) {
                return false; // Let PHP handle suppressed errors normally
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    /**
     * Validate that the runtime environment meets requirements.
     *
     * @throws RuntimeException If required extensions are missing
     */
    private function validateEnvironment() : void
    {
        $requiredExtensions = ['mbstring', 'json', 'pcre'];
        foreach ($requiredExtensions as $requiredExtension) {
            if (! extension_loaded($requiredExtension)) {
                throw new RuntimeException(sprintf("Required PHP extension '%s' is not loaded", $requiredExtension));
            }
        }
    }

    /**
     * Execute all registered boot callbacks.
     */
    private function runBootCallbacks() : void
    {
        foreach ($this->bootCallbacks as $bootCallback) {
            $bootCallback();
        }
    }

    /**
     * Check if the kernel has been booted.
     */
    public function isBooted() : bool
    {
        return $this->booted;
    }

    /**
     * Reset the boot state (useful for testing).
     */
    public function reset() : void
    {
        $this->booted = false;
    }
}
