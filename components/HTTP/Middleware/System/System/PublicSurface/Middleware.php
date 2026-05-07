<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\System\PublicSurface;

use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;

abstract class Middleware implements MiddlewareInterface
{
    abstract public function handle(RequestInterface $request, callable $next) : ResponseInterface;
}
