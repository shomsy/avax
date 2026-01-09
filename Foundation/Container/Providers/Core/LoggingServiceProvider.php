<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Core;

use Avax\Logging\ErrorHandler;
use Avax\Logging\LoggerFactory;
use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Service Provider for application logging and error handling.
 *
 * @see docs_md/Providers/Core/LoggingServiceProvider.md#quick-summary
 */
class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Registers logging services into the container.
     *
     * @return void
     * @see docs_md/Providers/Core/LoggingServiceProvider.md#method-register
     */
    public function register(): void
    {
        $this->app->singleton(abstract: LoggerFactory::class, concrete: LoggerFactory::class);

        // Register default logger with 'bootstrap-error-logs' channel
        $this->app->singleton(abstract: LoggerInterface::class, concrete: function () {
            $factory = $this->app->get(LoggerFactory::class);
            return $factory->createLoggerFor(channel: 'bootstrap-error-logs');
        });

        $this->app->singleton(abstract: ErrorHandler::class, concrete: function () {
            return new ErrorHandler(logger: $this->app->get(LoggerInterface::class));
        });
    }

    /**
     * Bootstraps global error handling.
     *
     * @return void
     * @see docs_md/Providers/Core/LoggingServiceProvider.md#method-boot
     */
    public function boot(): void
    {
        /** @var ErrorHandler $handler */
        $handler = $this->app->get(ErrorHandler::class);
        $handler->initialize();

        /** @var LoggerInterface $logger */
        $logger = $this->app->get(LoggerInterface::class);
        $logger->info(message: "Bootstrap logging initialized.");
    }
}
