<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\MiddlewarePipeline;

final readonly class Http implements HttpInterface
{
    public function __construct(private RouterInterface $router, private MiddlewarePipeline $middlewarePipeline) {}

    public function handle(RequestInterface $req) : ResponseInterface
    {
        return $this->middlewarePipeline->run($req, fn (RequestInterface $request) : ResponseInterface => $this->router->dispatch($request));
    }

    public function terminate(RequestInterface $request, ResponseInterface $response) : void {}
}
