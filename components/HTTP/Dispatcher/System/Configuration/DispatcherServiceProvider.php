<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Dispatcher\System\Flows\DispatchRouteAction\DispatchRouteAction;
use Avax\Components\HTTP\Dispatcher\System\PublicSurface\ControllerDispatcher;
use Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest\SecureRequestInputBuilder;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * DispatcherServiceProvider — registers HTTP Dispatcher component dependencies.
 *
 * Registers the controller/action resolution and argument resolution pipeline
 * that powers route dispatching.
 */
final class DispatcherServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // ControllerResolver — requires ResolveCallable via the Avax container
        $container->singleton(ControllerResolver::class, static fn (ContainerInterface $c) : ControllerResolver => new ControllerResolver(
            resolver: $c->get(\Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable::class),
        ));

        // ArgumentResolver — requires PSR container and SecureRequestInputBuilder
        $container->singleton(ArgumentResolver::class, static fn (ContainerInterface $c) : ArgumentResolver => new ArgumentResolver(
            container   : $c->get(PsrContainerInterface::class),
            inputBuilder: $c->get(SecureRequestInputBuilder::class),
        ));

        // DispatchRouteAction — requires ControllerResolver, ArgumentResolver, CreateHttpResponse
        $container->singleton(DispatchRouteAction::class, static fn (ContainerInterface $c) : DispatchRouteAction => new DispatchRouteAction(
            controllerResolver: $c->get(ControllerResolver::class),
            argumentResolver  : $c->get(ArgumentResolver::class),
            createHttpResponse: $c->get(\Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse::class),
        ));

        // ControllerDispatcher (PublicSurface) — requires DispatchRouteAction
        $container->singleton(ControllerDispatcher::class, static fn (ContainerInterface $c) : ControllerDispatcher => new ControllerDispatcher(
            dispatchRouteAction: $c->get(DispatchRouteAction::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
