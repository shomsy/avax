<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

final class Http implements HttpInterface
{
    public function __construct(private RouterInterface $router, private MiddlewarePipeline $pipeline)
    {
    }

    public function handle(RequestInterface $req): ResponseInterface
    {
        return $this->pipeline->run($req, fn ($request) => $this->router->dispatch($request));
    }

    public function terminate(RequestInterface $req, ResponseInterface $res): void
    {
    }
}
