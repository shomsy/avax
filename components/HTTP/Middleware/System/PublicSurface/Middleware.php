<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

abstract class Middleware implements MiddlewareInterface
{
    abstract public function handle(RequestInterface $request, callable $next): ResponseInterface;
}
