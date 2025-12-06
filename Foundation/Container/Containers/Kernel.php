<?php

declare(strict_types=1);

namespace Avax\Container\Containers;

use Avax\Exceptions\ValidationException;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\RouterInterface;
use Avax\Logging\ErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class Kernel
 *
 * Handles the HTTP request lifecycle, from middleware resolution to request dispatching
 * and error handling.
 * Encapsulates all logic required to process a web request.
 */
final class Kernel
{
    /**
     * @var array<callable> $middlewares
     */
    private array $middlewares = [];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly ErrorHandler    $errorHandler,
    ) {}

    /**
     * Executes the complete HTTP lifecycle: request creation, middleware, routing and response.
     *
     * @throws \JsonException
     */
    public function handleHttpRequest() : void
    {
        $request = Request::createFromGlobals();

        try {
            $this->registerMiddlewares();
            $response = $this->generateResponse(request: $request);
            $this->sendResponse(response: $response);
        } catch (Throwable $throwable) {
            $this->handleException(throwable: $throwable);
        }
    }

    /**
     * Registers global middleware defined in configuration.
     */
    private function registerMiddlewares() : void
    {
        $this->middlewares = $this->resolveConfiguredMiddlewares();
    }

    /**
     * Resolves middleware from configuration and dependency container.
     *
     * @return array<callable> Resolved middleware handlers
     */
    private function resolveConfiguredMiddlewares() : array
    {
        $middlewareClasses = config(key: 'middleware.global');

        return array_map(
            static fn(string $middlewareClass) => app()->get($middlewareClass),
            $middlewareClasses
        );
    }

    /**
     * Builds the middleware stack and dispatches the request to the router.
     *
     * @param Request $request
     *
     * @return ResponseInterface
     */
    private function generateResponse(Request $request) : ResponseInterface
    {
        $handler = fn(Request $request) : ResponseInterface => $this->router->resolve($request);
        foreach (array_reverse($this->middlewares) as $middleware) {
            $currentHandler = $handler;
            $handler        = static fn(Request $request) : ResponseInterface => $middleware->handle(
                $request,
                $currentHandler
            );
        }

        return $handler($request);
    }

    /**
     * Sends a fully formed PSR-7 response to the client.
     *
     * @param ResponseInterface $response
     */
    private function sendResponse(ResponseInterface $response) : void
    {
        if (! headers_sent()) {
            http_response_code($response->getStatusCode());

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(header: "{$name}: {$value}", replace: false);
                }
            }
        } else {
            error_log(message: '⚠️ Headers already sent, unable to modify HTTP response headers.');
        }

        echo (string) $response->getBody();
    }

    /**
     * Handles any uncaught exceptions and renders a proper error response.
     *
     * @param Throwable $throwable
     *
     * @throws \JsonException
     */
    private function handleException(Throwable $throwable) : void
    {
        if ($throwable instanceof ValidationException) {
            $response = $this->errorHandler->render(throwable: $throwable);
            $this->sendResponse(response: $response->toResponse());

            return;
        }

        $this->errorHandler->handle(throwable: $throwable);
    }

    /**
     * Allows runtime appending of additional middleware.
     *
     * @param array<callable> $middlewares
     */
    public function addMiddlewares(array $middlewares) : void
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
    }
}
