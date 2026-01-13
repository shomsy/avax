<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Core;

use Avax\Container\Providers\ServiceProvider;
use Avax\Logging\ErrorHandler;
use Avax\Logging\LoggerFactory;
use Psr\Log\LoggerInterface;

/**
 * Service Provider for application logging and error handling.
 *
 * @see docs/Providers/Core/LoggingServiceProvider.md#quick-summary
 */
class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Registers logging services into the container.
     *
     * @see docs/Providers/Core/LoggingServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: LoggerFactory::class, concrete: LoggerFactory::class);

        // Register default logger with 'bootstrap-error-logs' channel
        $this->app->singleton(abstract: LoggerInterface::class, concrete: function () {
            $factory = $this->app->get(id: LoggerFactory::class);

            return $factory->createLoggerFor(channel: 'bootstrap-error-logs');
        });

        $this->app->singleton(abstract: ErrorHandler::class, concrete: function () {
            return new ErrorHandler(logger: $this->app->get(id: LoggerInterface::class));
        });
    }

    /**
     * Bootstraps global error handling.
     *
     * @see docs/Providers/Core/LoggingServiceProvider.md#method-boot
     */
    public function boot() : void
    {
        /** @var ErrorHandler $handler */
        $handler = $this->app->get(id: ErrorHandler::class);
        $handler->initialize();

        /** @var LoggerInterface $logger */
        $logger = $this->app->get(id: LoggerInterface::class);
        $logger->info(message: 'Bootstrap logging initialized.');
    }
}
