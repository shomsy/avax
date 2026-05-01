<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\RouterInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use components\HTTP\Dispatcher\ControllerDispatcher;
use components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
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
        $controllerDispatcher          = new ControllerDispatcher(container: new RouteFacadeContainer());
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

        $routeRegistrar = new FrameworkRouteRegistrar();
        $services       = [
            RouterInterface::class => $routeRegistrar,
        ];
        $routeContainer = new RouteFacadeContainer(services: $services);

        try {
            $previousContainer = appInstance();
        } catch (RuntimeException) {
            $previousContainer = new RouteFacadeContainer();
        }

        appInstance(instance: $routeContainer);

        try {
            require $routesFile;
        } finally {
            appInstance(instance: $previousContainer);
        }

        return new self(
            registeredHttpRoutes: $routeRegistrar->collectRoutes(),
        );
    }

    public static function fromRouteDefinitions(callable $routeDefinitions): self
    {
        $routeRegistrar = new FrameworkRouteRegistrar();
        $routeDefinitions($routeRegistrar);

        return new self(
            registeredHttpRoutes: $routeRegistrar->collectRoutes(),
        );
    }

    public function __invoke(RuntimeRequest $request): ResponseInterface
    {
        $serverRequest = $this->readIncomingHttpRequest->read(request: $request);

        try {
            $matchedRoute = $this->matchHttpRoute->match(
                routes  : $this->registeredHttpRoutes,
                request : $serverRequest,
            );

            return $this->runHttpRoute->run(matchedRoute: $matchedRoute);
        } catch (RouteNotFoundException) {
            if ($this->registeredHttpRoutes->hasFallback()) {
                return $this->runHttpRoute->runFallback(
                    fallback : $this->registeredHttpRoutes->fallback() ?? static fn (): string => '',
                    request  : $serverRequest,
                );
            }

            return $this->responseFactory->createErrorResponse(
                statusCode : 404,
                message    : 'Route not found',
            );
        } catch (MethodNotAllowedException $exception) {
            return $this->responseFactory->createErrorResponse(
                statusCode : 405,
                message    : $exception->getMessage(),
            );
        }
    }
}
