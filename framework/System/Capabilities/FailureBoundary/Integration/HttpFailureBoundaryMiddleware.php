<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Integration;

use Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
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

        return \Avax\Components\HTTP\Response\System\PublicSurface\Response::json(
            $result,
            200,
        );
    }
}
