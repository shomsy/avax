<?php

declare(strict_types=1);

namespace Avax\Container\Http;

use Avax\Container\Container;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Lightweight HTTP application runtime.
 */
final readonly class HttpApplication
{
    public function __construct(private Container $container, private RouterRuntimeInterface $router) {}

    /**
     * Expose the underlying container instance.
     */
    public function getContainer() : Container
    {
        return $this->container;
    }

    /**
     * Run the HTTP lifecycle.
     */
    public function run() : ResponseInterface
    {
        $this->container->beginScope();

        try {
            $request = Request::createFromGlobals();
            $this->container->instance(abstract: Request::class, instance: $request);

            $response = $this->router->resolve(request: $request);

            // Send if possible
            if (method_exists($response, 'send')) {
                $response->send();
            }

            return $response;
        } finally {
            $this->container->endScope();
        }
    }
}
