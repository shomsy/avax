<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\Builders;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Dispatcher\System\Flows\DispatchRouteAction\DispatchRouteAction;
use Avax\Components\HTTP\Dispatcher\System\PublicSurface\ControllerDispatcher;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest\SecureRequestInputBuilder;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Flows\HandleIncomingHttp\DispatchConfiguredRoute;
use Avax\Framework\System\Flows\HandleIncomingHttp\FrameworkRouteRegistrar;
use Avax\Framework\System\Flows\HandleIncomingHttp\MatchHttpRoute;
use Avax\Framework\System\Flows\HandleIncomingHttp\ReadIncomingHttpRequest;
use Avax\Framework\System\Flows\HandleIncomingHttp\RegisteredHttpRoutes;
use Avax\Framework\System\Flows\HandleIncomingHttp\RouteFacadeContainer;
use Avax\Framework\System\Flows\HandleIncomingHttp\RunHttpRoute;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * Configuration builder for assembling DispatchConfiguredRoute.
 *
 * Assembly belongs in Configuration, not in Flows.
 * This builder constructs the full object graph for route dispatch
 * and returns a ready-to-execute DispatchConfiguredRoute.
 */
final class BuildDispatchConfiguredRoute
{
    public function __construct(
        private Filesystem|null $filesystem = null,
    ) {
    }

    /**
     * Assemble DispatchConfiguredRoute from a routes file path.
     *
     * @throws FrameworkMisconfigured When routes file does not exist
     */
    public function fromRoutesFile(string $routesFile, CreateHttpResponse $createHttpResponse): DispatchConfiguredRoute
    {
        $filesystem = $this->filesystem ?? new Filesystem();

        if (! $filesystem->isFile($routesFile)) {
            throw new FrameworkMisconfigured(
                message: sprintf('HTTP routes file "%s" does not exist.', $routesFile),
            );
        }

        $frameworkRouteRegistrar = new FrameworkRouteRegistrar();
        $services = [
            RouterInterface::class => $frameworkRouteRegistrar,
        ];
        $routeContainer = new RouteFacadeContainer(services: $services);

        try {
            $previousContainer = appInstance();
        } catch (Exception) {
            $previousContainer = null;
        }

        try {
            $routeDefinitions = require $routesFile;

            if (is_callable(value: $routeDefinitions)) {
                $routeDefinitions($frameworkRouteRegistrar, $createHttpResponse);
            }
        } finally {
            if ($previousContainer !== null) {
                appInstance(instance: $previousContainer);
            }
        }

        return $this->fromRegisteredRoutes(
            createHttpResponse   : $createHttpResponse,
            registeredHttpRoutes : $frameworkRouteRegistrar->collectRoutes(),
        );
    }

    /**
     * Assemble DispatchConfiguredRoute from route definition callbacks.
     */
    public function fromRouteDefinitions(callable $routeDefinitions, CreateHttpResponse $createHttpResponse): DispatchConfiguredRoute
    {
        $frameworkRouteRegistrar = new FrameworkRouteRegistrar();
        $routeDefinitions($frameworkRouteRegistrar);

        return $this->fromRegisteredRoutes(
            createHttpResponse   : $createHttpResponse,
            registeredHttpRoutes : $frameworkRouteRegistrar->collectRoutes(),
        );
    }

    /**
     * Assemble DispatchConfiguredRoute from already-collected routes.
     */
    public function fromRegisteredRoutes(
        CreateHttpResponse $createHttpResponse,
        RegisteredHttpRoutes $registeredHttpRoutes,
    ): DispatchConfiguredRoute {
        $container            = new RouteFacadeContainer();
        $resolveCallable      = new ResolveCallable(container: clone $container);
        $controllerResolver   = new ControllerResolver(resolver: $resolveCallable);
        $argumentResolver     = new ArgumentResolver(container: $container, inputBuilder: new SecureRequestInputBuilder());
        $dispatchRouteAction  = new DispatchRouteAction(
            controllerResolver: $controllerResolver,
            argumentResolver  : $argumentResolver,
            createHttpResponse: $createHttpResponse,
        );
        $controllerDispatcher = new ControllerDispatcher(dispatchRouteAction: $dispatchRouteAction);

        return new DispatchConfiguredRoute(
            createHttpResponse     : $createHttpResponse,
            registeredHttpRoutes   : $registeredHttpRoutes,
            readIncomingHttpRequest: new ReadIncomingHttpRequest(),
            matchHttpRoute         : new MatchHttpRoute(new MatchRoute()),
            runHttpRoute           : new RunHttpRoute(controllerDispatcher: $controllerDispatcher),
        );
    }
}
