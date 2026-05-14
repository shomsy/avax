<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Events\System\Capabilities\HealthCheck\CheckEventsHealth;
use Avax\Components\Operations\Events\System\Capabilities\InvokeEventListener\InvokeEventListener;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners\ResolveEventListeners;
use Avax\Components\Operations\Events\System\Foundation\CompiledListenerRegistry;
use Avax\Components\Operations\Events\System\Foundation\EventEmitter;
use Avax\Components\Operations\Events\System\Foundation\GlobalEventListenerState;

/**
 * EventsServiceProvider — registers event system dependencies.
 */
final class EventsServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Listener registry — mutable registration store
        $container->singleton(ListenerRegistry::class, static fn () : ListenerRegistry => new ListenerRegistry());

        // Compiled listener registry — frozen DSL + attribute merge
        $container->singleton(CompiledListenerRegistry::class, static fn () : CompiledListenerRegistry => new CompiledListenerRegistry());

        // Resolve event listeners — class-string to callable resolution
        $container->singleton(ResolveEventListeners::class, static fn (ContainerInterface $c) : ResolveEventListeners => new ResolveEventListeners(container: $c));

        // Invoke event listener — single listener invocation (no constructor params)
        $container->singleton(InvokeEventListener::class, static fn () : InvokeEventListener => new InvokeEventListener());

        // Event emitter — core dispatcher
        $container->singleton(EventEmitter::class, static fn (ContainerInterface $c) : EventEmitter => new EventEmitter(
            registry: $c->get(CompiledListenerRegistry::class),
            resolver: $c->get(ResolveEventListeners::class),
            invoker : $c->get(InvokeEventListener::class),
        ));

        // Health check
        $container->singleton(CheckEventsHealth::class, static fn () : CheckEventsHealth => new CheckEventsHealth());
    }

    public function boot(ContainerInterface $container) : void
    {
        // Wire global static state for backward compatibility
        GlobalEventListenerState::setRegistry($container->get(ListenerRegistry::class));
        GlobalEventListenerState::setEmitter($container->get(EventEmitter::class));
    }
}
