<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

interface MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next) : ResponseInterface;
}
