<?php

declare(strict_types=1);

namespace Avax\Components\HTTP;

use Avax\Components\HTTP\Middleware\RequestHandlerInterface;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\RouterRuntimeInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use RuntimeException;

/**
 * Resolves the matched route from an incoming HTTP request and dispatches it to the target controller.
 *
 * @internal
 */
readonly class ResolveRouteFromHttpRequest implements RequestHandlerInterface
{
    public function __construct(private RouterRuntimeInterface $router) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException
     */
    public function handle(RequestInterface $request) : ResponseInterface
    {
        if (! $request instanceof ServerRequest) {
            throw new RuntimeException(
                message: 'HttpKernel requires an internal Avax HTTP ServerRequest instance for router execution.'
            );
        }

        return $this->router->resolve(request: $request);
    }
}
