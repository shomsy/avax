<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\SyncQueue;
use Avax\Components\Operations\Queue\System\Flows\Dispatch\DispatchJob;
use Avax\Components\Operations\Queue\System\PublicSurface\Dispatcher;

/**
 * QueueServiceProvider — registers queue component dependencies.
 */
final class QueueServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Queue broker — default sync queue implementation
        $container->singleton(QueueBroker::class, static fn () : QueueBroker => new SyncQueue());

        // Job registry — mutable handler registry
        $container->singleton(JobRegistry::class, static fn () : JobRegistry => new JobRegistry());

        // Dispatch job flow — requires job registry
        $container->singleton(DispatchJob::class, static fn (ContainerInterface $c) : DispatchJob => new DispatchJob(
            jobRegistry: $c->get(JobRegistry::class),
        ));

        // Dispatcher public surface
        $container->singleton(Dispatcher::class, static fn (ContainerInterface $c) : Dispatcher => new Dispatcher(
            dispatchJob: $c->get(DispatchJob::class),
            queueBroker: $c->get(QueueBroker::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
