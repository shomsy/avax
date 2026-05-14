<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Integration;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\MiddlewareInterface;
use Avax\Framework\System\Capabilities\FailureBoundary\Flows\RunProtectedAction\RunProtectedAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;

/**
 * HttpFailureBoundaryMiddleware — Wraps the HTTP request pipeline in a failure boundary.
 *
 * This middleware should be placed early in the stack so it catches all downstream failures.
 */
final readonly class HttpFailureBoundaryMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RunProtectedAction $runProtected,
    ) {
    }

    public function handle(RequestInterface $request, callable $n): ResponseInterface
    {
        $context = FailureContext::forHttp($request);

        $result = $this->runProtected->run(
            action: static fn () => $n($request),
            context: $context,
        );

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        return Response::json(
            $result,
            200,
        );
    }
}
