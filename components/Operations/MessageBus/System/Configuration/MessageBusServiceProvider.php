<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\QueryBus;
use Avax\Components\Operations\MessageBus\System\PublicSurface\MessageBus;

/**
 * MessageBusServiceProvider — registers message bus component dependencies.
 */
final class MessageBusServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Command bus — dispatches commands to single handlers
        $container->singleton(CommandBus::class, static fn () : CommandBus => new CommandBus());

        // Query bus — dispatches queries to single handlers
        $container->singleton(QueryBus::class, static fn () : QueryBus => new QueryBus());

        // Event bus — dispatches events to multiple listeners
        $container->singleton(EventBus::class, static fn () : EventBus => new EventBus());

        // MessageBus PublicSurface
        $container->singleton(MessageBus::class, static fn (ContainerInterface $c) : MessageBus => new MessageBus(
            commandBus: $c->get(CommandBus::class),
            queryBus  : $c->get(QueryBus::class),
            eventBus  : $c->get(EventBus::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // Wire the global static state
        $container->get(MessageBus::class);
    }
}
