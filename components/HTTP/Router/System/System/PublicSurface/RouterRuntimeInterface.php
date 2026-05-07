<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\System\PublicSurface;

use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;

/**
 * Router runtime interface — resolves matched routes and dispatches actions.
 */
interface RouterRuntimeInterface
{
    /**
     * Resolve the matched route from an incoming request and dispatch it.
     */
    public function resolve(RequestInterface $request) : ResponseInterface;
}
