<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Logs middleware pipeline execution lifecycle.
 */
final class MiddlewarePipelineLogger
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Logs the start of middleware execution.
     *
     * @param RequestInterface $request The incoming request.
     */
    public function logStart(RequestInterface $request) : void
    {
        $this->logger->info(
            message: '⚙️ Starting middleware pipeline',
            context: ['uri' => (string) $request->getUri()]
        );
    }

    /**
     * Logs a single middleware execution.
     *
     * @param string $middlewareClass Fully qualified class name.
     */
    public function logMiddleware(string $middlewareClass) : void
    {
        $this->logger->debug(
            message: sprintf('⛓ Executing middleware: %s', $middlewareClass)
        );
    }

    /**
     * Logs the end of middleware pipeline.
     */
    public function logEnd() : void
    {
        $this->logger->info(message: '✅ Finished middleware pipeline');
    }
}
