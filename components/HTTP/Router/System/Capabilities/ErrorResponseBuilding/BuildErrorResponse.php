<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\ErrorResponseBuilding;

use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;

final readonly class BuildErrorResponse
{
    public function __construct(
        private CreateHttpResponse $createHttpResponse,
    ) {
    }

    public function notFound(): Response
    {
        return $this->createHttpResponse->notFound(message: 'Not Found');
    }

    /**
     * @param list<RouteMethod> $allowedMethods
     */
    public function methodNotAllowed(array $allowedMethods): Response
    {
        $methods = array_map(
            static fn (RouteMethod $m): string => $m->value,
            $allowedMethods,
        );

        return $this->createHttpResponse->methodNotAllowed(allowedMethods: $methods);
    }
}
