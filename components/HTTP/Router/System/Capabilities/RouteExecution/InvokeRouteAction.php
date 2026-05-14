<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteExecution;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Router\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;

/**
 * Invokes a matched route's action with resolved parameters.
 */
final readonly class InvokeRouteAction
{
    public function __construct(
        private NormalizeControllerResult $responseNormalizer,
    ) {
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function invoke(mixed $action, RequestInterface $request, array $parameters) : Response
    {
        if (! is_callable($action)) {
            throw new RouterFailure('Invalid route action');
        }

        $result = $action($request, ...array_values($parameters));

        return $this->responseNormalizer->normalize($result);
    }
}
