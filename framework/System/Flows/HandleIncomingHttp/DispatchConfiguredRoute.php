<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
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
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Exception;
use Psr\Http\Message\ResponseInterface;

final readonly class DispatchConfiguredRoute
{
    public function __construct(
        private CreateHttpResponse $createHttpResponse,
        private RegisteredHttpRoutes $registeredHttpRoutes,
        private ReadIncomingHttpRequest $readIncomingHttpRequest,
        private MatchHttpRoute $matchHttpRoute,
        private RunHttpRoute $runHttpRoute,
    ) {
    }

    /**
     * @throws FrameworkMisconfigured When routes file does not exist
     */
    public static function fromRoutesFile(string $routesFile, CreateHttpResponse $createHttpResponse): self
    {
        if (! is_file(filename: $routesFile)) {
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
                $routeDefinitions($frameworkRouteRegistrar);
            }
        } finally {
            if ($previousContainer !== null) {
                appInstance(instance: $previousContainer);
            }
        }

        return self::fromRegisteredRoutes(
            createHttpResponse  : $createHttpResponse,
            registeredHttpRoutes: $frameworkRouteRegistrar->collectRoutes(),
        );
    }

    public static function fromRouteDefinitions(callable $routeDefinitions, CreateHttpResponse $createHttpResponse): self
    {
        $frameworkRouteRegistrar = new FrameworkRouteRegistrar();
        $routeDefinitions($frameworkRouteRegistrar);

        return self::fromRegisteredRoutes(
            createHttpResponse  : $createHttpResponse,
            registeredHttpRoutes: $frameworkRouteRegistrar->collectRoutes(),
        );
    }

    public static function fromRegisteredRoutes(
        CreateHttpResponse $createHttpResponse,
        RegisteredHttpRoutes $registeredHttpRoutes,
    ): self {
        $container            = new RouteFacadeContainer();
        $resolveCallable      = new ResolveCallable(container: clone $container);
        $controllerResolver   = new ControllerResolver(resolver: $resolveCallable);
        $argumentResolver = new ArgumentResolver(container: $container, inputBuilder: new SecureRequestInputBuilder());
        $dispatchRouteAction  = new DispatchRouteAction(
            controllerResolver: $controllerResolver,
            argumentResolver  : $argumentResolver,
            createHttpResponse: $createHttpResponse,
        );
        $controllerDispatcher = new ControllerDispatcher(dispatchRouteAction: $dispatchRouteAction);

        return new self(
            createHttpResponse   : $createHttpResponse,
            registeredHttpRoutes : $registeredHttpRoutes,
            readIncomingHttpRequest: new ReadIncomingHttpRequest(),
            matchHttpRoute       : new MatchHttpRoute(new MatchRoute()),
            runHttpRoute         : new RunHttpRoute(controllerDispatcher: $controllerDispatcher),
        );
    }

    public function __invoke(RuntimeRequest $runtimeRequest): ResponseInterface
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

            return $this->createHttpResponse->error(
                message: 'Route not found',
                status : 404,
            );
        } catch (MethodNotAllowedException $exception) {
            return $this->createHttpResponse->error(
                message: $exception->getMessage(),
                status : 405,
            );
        }
    }
}
