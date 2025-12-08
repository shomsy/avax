<?php

declare(strict_types=1);

namespace Foundation\HTTP\Session\Middleware;

use Avax\HTTP\Session\Observability\MetricCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * MetricsMiddleware
 *
 * PSR-15 compatible middleware that measures session-related request metrics.
 * - Latency measurement
 * - Session hit/miss tracking
 * - Error count
 *
 * @package Foundation\HTTP\Session\Middleware
 */
final class MetricsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly MetricCollector $metrics
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);

        try {
            $response = $handler->handle($request);
            $duration = microtime(true) - $start;

            $this->metrics->observeLatency($duration);
            $this->metrics->increment('session_ops_total');

            return $response;
        } catch (\Throwable $e) {
            $this->metrics->increment('session_errors_total');
            throw $e;
        }
    }
}
