<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\AfterResponse\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\AfterResponse\System\Capabilities\Tasks\AfterResponseQueue;

/**
 * AfterResponseServiceProvider — registers HTTP AfterResponse component dependencies.
 *
 * Registers the AfterResponseQueue as a singleton so callbacks enqueued during
 * a request are executed after the response is sent.
 */
final class AfterResponseServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // AfterResponseQueue — no dependencies
        $container->singleton(AfterResponseQueue::class, static fn () : AfterResponseQueue => new AfterResponseQueue());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
