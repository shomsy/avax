<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\Operations\Logging\System\Capabilities\HealthCheck\CheckLoggingHealth;
use Avax\Components\Operations\Logging\System\Capabilities\Logger\Logger;
use Psr\Log\LoggerInterface;

/**
 * LoggingServiceProvider — registers logging component dependencies.
 */
final class LoggingServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Logger — PSR-3 compliant logger
        $container->singleton(Logger::class, static fn () : Logger => new Logger());

        // PSR-3 alias
        $container->singleton(LoggerInterface::class, static fn (ContainerInterface $c) : LoggerInterface => $c->get(Logger::class));

        // Health check
        $container->singleton(CheckLoggingHealth::class, static fn () : CheckLoggingHealth => new CheckLoggingHealth());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
