<?php

declare(strict_types=1);

namespace Avax\Container\Containers;

use Avax\Logging\ErrorHandler;
use Throwable;

/**
 * Class Application
 *
 * Final class ensures that the Application cannot be inherited from,
 * promoting design integrity and stability.
 * This class orchestrates the application lifecycle, managing bootstrapping,
 * request handling, and error management.
 */
final readonly class Application
{
    /**
     * Run the application lifecycle.
     *
     * Bootstrap the container and register services.
     * Handle incoming requests. Any uncaught exceptions are passed to the error handler.
     * Using a Container instance to manage dependencies and configurations.
     *
     */
    public function run() : void
    {
        try {
            /* @var Kernel $kernel */
            $kernel = app()->get(id: Kernel::class);
            $kernel->handleHttpRequest(); // Initiates the main request-response lifecycle via the Kernel
        } catch (Throwable $throwable) {
            // Delegates error handling to the ErrorHandler
            // The app function should be an IoC container lookup to get the ErrorHandler instance
            app()->get(id: ErrorHandler::class)->handle($throwable);
        }
    }
}