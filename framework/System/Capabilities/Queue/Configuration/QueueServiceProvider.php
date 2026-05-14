<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Queue\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\PdoFailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\MemoryQueue\MemoryQueue;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Flows\RunWorkerLoop\RunWorkerLoop;
use Avax\Framework\System\Capabilities\Queue\RegisterQueueCommands;
use Closure;
use PDO;

/**
 * QueueServiceProvider — registers queue processing dependencies.
 *
 * All queue commands receive injected broker, failed store, callable resolver, and worker loop factory.
 * No direct instantiation of PdoFailedJobsStore, MemoryQueue, PDO, or RunWorkerLoop.
 */
final class QueueServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Queue broker — memory-based default, can be swapped for Redis/SQS etc.
        $container->singleton(MemoryQueue::class, static fn () : MemoryQueue => new MemoryQueue(),
        );
        $container->alias(QueueBroker::class, MemoryQueue::class);

        // PDO connection — SQLite default, configurable through env
        $container->singleton(PDO::class, static fn () : PDO => new PDO('sqlite:' . ($_ENV['QUEUE_DB_PATH'] ?? ':memory:')),
        );

        // Failed jobs store — PDO-backed
        $container->singleton(FailedJobsStore::class, static fn (ContainerInterface $c) : FailedJobsStore => new PdoFailedJobsStore($c->get(PDO::class)),
        );

        // ResolveCallable — for resolving job handler classes through DI
        $container->singleton(ResolveCallable::class, static fn (ContainerInterface $c) : ResolveCallable => new ResolveCallable(container: $c),
        );

        // Worker loop factory closure — creates RunWorkerLoop instances on demand
        $container->singleton('queue.worker_loop_factory', static fn () : Closure => static fn (MemoryQueue $broker, Closure $handler, int $sleep) : RunWorkerLoop => new RunWorkerLoop(broker: $broker, handler: $handler, sleepMicroseconds: $sleep),
        );

        // RegisterQueueCommands — all dependencies injected, no direct instantiation
        $container->singleton(RegisterQueueCommands::class, static fn (ContainerInterface $c) : RegisterQueueCommands => new RegisterQueueCommands(
            broker          : $c->get(MemoryQueue::class),
            failedStore     : $c->get(FailedJobsStore::class),
            callableResolver: $c->get(ResolveCallable::class),
            createWorkerLoop: $c->get('queue.worker_loop_factory'),
        ),
        );
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed for queue component
    }
}
