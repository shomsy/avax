<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

final class Http implements HttpInterface
{
    public function __construct(private RouterInterface $r, private MiddlewarePipeline $p) {}

    public function handle(RequestInterface $req) : ResponseInterface
    {
        return $this->p->run($req, fn ($req) => $this->r->dispatch($req));
    }

    public function terminate(RequestInterface $req, ResponseInterface $res) : void {}
}
