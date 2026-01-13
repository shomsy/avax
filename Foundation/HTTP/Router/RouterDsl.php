<?php

declare(strict_types=1);

namespace Avax\HTTP\Router;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\AttributeRouteRegistrar;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteBuilder;
use Avax\HTTP\Router\Routing\RouteGroupAttributesConfigurator;
use Avax\HTTP\Router\Routing\RouteGroupContext;
use Avax\HTTP\Router\Routing\RouteGroupStack;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use Avax\HTTP\Router\Routing\RouterRegistrar;
use Avax\HTTP\Router\Support\FallbackManager;
use Avax\HTTP\Router\Support\RouteCollector;
use Avax\HTTP\Router\Support\RouteRegistry;
use Closure;
use LogicException;

/**
 * Router DSL surface responsible for defining routes and fallbacks.
 *
 * This class keeps all registration logic isolated from the runtime router.
 */
final readonly class RouterDsl implements RouterInterface
{
    public function __construct(
        private RouterRegistrar      $registrar,
        private HttpRequestRouter    $router,
        private ControllerDispatcher $controllerDispatcher,
        private FallbackManager      $fallbackManager,
        private RouteGroupStack      $groupStack,
        private RouteRegistry        $registry,
    ) {}

    public function get(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('Route path cannot be empty in get() method');
        }
        return $this->register(method: HttpMethod::GET->value, path: $path, action: $action);
    }

    private function register(string $method, string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        if (empty($path)) {
            throw new \InvalidArgumentException("Route path cannot be empty for method {$method}");
        }

        $builder = RouteBuilder::make(method: $method, path: $path);
        $builder->action(action: $action);

        // Apply current group context (prefixes, middleware, etc.)
        $builder = $this->groupStack->applyTo(builder: $builder);

        return new RouteRegistrarProxy(
            router  : $this->router,
            builder : $builder,
            registry: $this->registry
        );
    }

    public function post(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::POST->value, path: $path, action: $action);
    }

    public function put(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::PUT->value, path: $path, action: $action);
    }

    public function patch(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::PATCH->value, path: $path, action: $action);
    }

    public function delete(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::DELETE->value, path: $path, action: $action);
    }

    public function options(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::OPTIONS->value, path: $path, action: $action);
    }

    public function head(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::HEAD->value, path: $path, action: $action);
    }

    public function any(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return $this->register(method: HttpMethod::ANY->value, path: $path, action: $action);
    }

    public function anyExpanded(string $path, callable|array|string $action) : array
    {
        $proxies = [];

        foreach (HttpMethod::cases() as $method) {
            // Skip ANY method in expanded version
            if ($method !== HttpMethod::ANY) {
                $proxies[] = $this->register(method: $method->value, path: $path, action: $action);
            }
        }

        return $proxies;
    }

    public function fallback(callable|array|string $handler) : void
    {
        $callable = is_callable(value: $handler)
            ? $handler
            : fn(Request $request) => $this->controllerDispatcher->dispatch(action: $handler, request: $request);

        // Unified fallback handling through FallbackManager only
        $this->fallbackManager->set(handler: $callable);

        // Set in registry for DSL execution (used during route file loading)
        $this->registry->setFallback(handler: $callable);
    }

    public function registerAttributes(object|string $controller) : void
    {
        (new AttributeRouteRegistrar(router: $this->router))->register(controller: $controller);
    }

    public function name(string $prefix) : self
    {
        $context = $this->groupStack->current();

        if ($context === null) {
            throw new LogicException(message: 'Cannot call ->name() outside of a route group context.');
        }

        $context->setNamePrefix(prefix: $prefix);

        return $this;
    }

    public function domain(string $domain) : self
    {
        $this->groupStack->current()?->setDomain(domain: $domain);

        return $this;
    }

    public function authorize(string $policy) : self
    {
        $this->groupStack->current()?->setAuthorization(authorization: $policy);

        return $this;
    }

    public function group(array $attributes, Closure $callback) : void
    {
        $context = new RouteGroupContext;

        (new RouteGroupAttributesConfigurator)->apply(
            attributes: $attributes,
            context   : $context
        );

        $this->groupStack->push(group: $context);

        try {
            $callback($this);
        } finally {
            $this->groupStack->pop();
        }
    }

    public function prefix(string $prefix) : self
    {
        $this->groupStack->current()?->setPrefix(prefix: $prefix);

        return $this;
    }

    public function middleware(array $middleware) : self
    {
        $this->groupStack->current()?->addMiddleware(middleware: $middleware);

        return $this;
    }

    public function where(array $constraints) : self
    {
        $this->groupStack->current()?->addConstraints(constraints: $constraints);

        return $this;
    }

    public function defaults(array $defaults) : self
    {
        $this->groupStack->current()?->addDefaults(defaults: $defaults);

        return $this;
    }

    public function attributes(array $attributes) : self
    {
        $this->groupStack->current()?->addAttributes(attributes: $attributes);

        return $this;
    }

    /**
     * Handle dynamic method calls for HTTP methods not explicitly defined.
     * This prevents creation of routes with empty paths when invalid methods are called.
     *
     * @param string $method The method name being called
     * @param array $arguments The arguments passed to the method
     *
     * @throws \BadMethodCallException When an invalid HTTP method is called
     */
    public function __call(string $method, array $arguments): mixed
    {
        // Check if this might be an HTTP method (all uppercase)
        if (strtoupper($method) === $method && strlen($method) > 0) {
            throw new \BadMethodCallException("HTTP method '{$method}' is not supported or route path is empty");
        }

        throw new \BadMethodCallException("Method '{$method}' does not exist on RouterDsl");
    }
}