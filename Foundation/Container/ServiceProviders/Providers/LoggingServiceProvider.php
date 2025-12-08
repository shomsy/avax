<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\HTTP\Session\Contracts\SessionLoggerInterface;
use Avax\Logging\ErrorHandler;
use Avax\Logging\LoggerFactory;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Registers all necessary services related to logging.
     *
     * @throws \Exception
     */
    public function register() : void
    {
        $this->dependencyInjector->singleton(
            abstract: LoggerFactory::class,
            concrete: static fn() : LoggerFactory => new LoggerFactory()
        );

        $this->dependencyInjector->singleton(
            abstract: LoggerInterface::class,
            concrete: fn() : LoggerInterface => $this->resolveDependency(abstract: LoggerFactory::class)->create()
        );

        $this->dependencyInjector->singleton(
            abstract: ErrorHandler::class,
            concrete: fn() : ErrorHandler => new ErrorHandler(
                logger: $this->resolveDependency(abstract: LoggerInterface::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: SessionLoggerInterface::class,
            concrete: fn() => $this
                ->resolveDependency(abstract: LoggerFactory::class)
                ->createLoggerFor(channel: 'session')
        );
    }

    /**
     * Resolves a dependency from the container with additional validation.
     *
     * @template T
     * @param class-string<T> $abstract The class name of the dependency.
     *
     * @return T
     */
    private function resolveDependency(string $abstract) : mixed
    {
        if (! $this->dependencyInjector->has(id: $abstract)) {
            throw new RuntimeException(message: sprintf("Action '%s' is not registered in the container.", $abstract));
        }

        return $this->dependencyInjector->get(id: $abstract);
    }

    public function boot() : void {}
}
