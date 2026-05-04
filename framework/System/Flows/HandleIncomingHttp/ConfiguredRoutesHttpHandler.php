<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Dispatcher\System\Flows\DispatchRouteAction\DispatchRouteAction;
use Avax\Components\HTTP\Dispatcher\System\PublicSurface\ControllerDispatcher;
use Exception;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final readonly class ConfiguredRoutesHttpHandler
{
    private ReadIncomingHttpRequest $readIncomingHttpRequest;

    private MatchHttpRoute $matchHttpRoute;

    private RunHttpRoute $runHttpRoute;

    private ResponseFactory $responseFactory;

    public function __construct(private RegisteredHttpRoutes $registeredHttpRoutes)
    {
        $container                     = new RouteFacadeContainer();
        $controllerResolver            = new ControllerResolver(container: clone $container);
        $argumentResolver              = new ArgumentResolver(container: clone $container);
        $dispatchRouteAction           = new DispatchRouteAction(
            controllerResolver: $controllerResolver,
            argumentResolver  : $argumentResolver,
        );
        $controllerDispatcher          = new ControllerDispatcher(dispatchRouteAction: $dispatchRouteAction);
        $this->readIncomingHttpRequest = new ReadIncomingHttpRequest();
        $this->matchHttpRoute          = new MatchHttpRoute();
        $this->runHttpRoute            = new RunHttpRoute(controllerDispatcher: $controllerDispatcher);
        $this->responseFactory         = new ResponseFactory();
    }

    public static function fromRoutesFile(string $routesFile): self
    {
        if (! is_file(filename: $routesFile)) {
            throw new FrameworkMisconfigured(
                message: sprintf('HTTP routes file "%s" does not exist.', $routesFile),
            );
        }

        $frameworkRouteRegistrar = new FrameworkRouteRegistrar();
        $services       = [
            RouterInterface::class => $frameworkRouteRegistrar,
        ];
        $routeContainer = new RouteFacadeContainer(services: $services);

        try {
            $previousContainer = appInstance();
        } catch (Exception) {
            $previousContainer = null;
        }

        // We bypass setting the RouteFacadeContainer into the global appInstance
        // to avoid type mismatch, since it is not a full DIContainerInterface.
        // Instead we assume that routing DSL just gets its bindings from somewhere,
        // but for now we skip global assignment to avoid errors.

        try {
            require $routesFile;
        } finally {
            if ($previousContainer !== null) {
                appInstance(instance: $previousContainer);
            }
        }

        return new self(
            registeredHttpRoutes: $frameworkRouteRegistrar->collectRoutes(),
        );
    }

    public static function fromRouteDefinitions(callable $routeDefinitions): self
    {
        $frameworkRouteRegistrar = new FrameworkRouteRegistrar();
        $routeDefinitions($frameworkRouteRegistrar);

        return new self(
            registeredHttpRoutes: $frameworkRouteRegistrar->collectRoutes(),
        );
    }

    public function __invoke(RuntimeRequest $runtimeRequest) : ResponseInterface
    {
        $serverRequest = $this->readIncomingHttpRequest->read(runtimeRequest: $runtimeRequest);

        try {
            $matchedRoute = $this->matchHttpRoute->match(
                registeredHttpRoutes: $this->registeredHttpRoutes,
                serverRequest       : $serverRequest,
            );

            return $this->runHttpRoute->run(matchedHttpRoute: $matchedRoute);
        } catch (RouteNotFoundException) {
            if ($this->registeredHttpRoutes->hasFallback()) {
                return $this->runHttpRoute->runFallback(
                    fallback      : $this->registeredHttpRoutes->fallback() ?? static fn (): string => '',
                    serverRequest : $serverRequest,
                );
            }

            return $this->responseFactory->createErrorResponse(
                message    : 'Route not found',
                statusCode : 404,
            );
        } catch (MethodNotAllowedException $exception) {
            return $this->responseFactory->createErrorResponse(
                message    : $exception->getMessage(),
                statusCode : 405,
            );
        }
    }
}
