<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\ErrorResponseBuilding;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;

final readonly class BuildErrorResponse
{
    public function notFound() : Response
    {
        return Response::json(['error' => 'Not Found'], 404);
    }

    /**
     * @param list<RouteMethod> $allowedMethods
     */
    public function methodNotAllowed(array $allowedMethods) : Response
    {
        $methods = array_map(
            static fn (RouteMethod $m) : string => $m->value,
            $allowedMethods,
        );

        return Response::json(
            ['error' => 'Method Not Allowed', 'allowed' => $methods],
            405,
        )->withHeader('Allow', implode(', ', $methods));
    }
}
