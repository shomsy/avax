<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\DispatchRoute;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final class InvokeRouteAction
{
    public function invoke(mixed $action, RequestInterface $request) : ResponseInterface
    {
        if (is_callable($action)) {
            return $action($request);
        }

        throw new RouteDispatchFailed('Action is not callable');
    }
}
