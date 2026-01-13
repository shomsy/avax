<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class TracingMiddleware implements MiddlewareInterface
{
    private array $metrics = [
        'total_requests' => 0,
        'total_latency_ms' => 0,
        'uptime_seconds' => 0,
    ];

    private float $startTime;

    public function __construct(
        private LoggerInterface $logger,
        private string $requestIdHeader = 'X-Request-ID'
    ) {
        $this->startTime = microtime(true);
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $requestId = $this->generateRequestId();
        $startTime = microtime(true);

        // Add request ID to request
        $request = $request->withHeader($this->requestIdHeader, $requestId);

        $this->logger->info('Request started', [
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'query' => $request->getUri()->getQuery(),
            'headers' => $this->getSafeHeaders($request),
        ]);

        try {
            $response = $next($request);

            $latency = (microtime(true) - $startTime) * 1000; // ms

            // Update metrics
            $this->metrics['total_requests']++;
            $this->metrics['total_latency_ms'] += $latency;
            $this->metrics['uptime_seconds'] = microtime(true) - $this->startTime;

            $this->logger->info('Request completed', [
                'request_id' => $requestId,
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
                'status' => $response->getStatusCode(),
                'latency_ms' => round($latency, 2),
                'response_size_bytes' => strlen((string) $response->getBody()),
            ]);

            // Add request ID to response
            return $response->withHeader($this->requestIdHeader, $requestId);

        } catch (\Throwable $e) {
            $latency = (microtime(true) - $startTime) * 1000;

            $this->logger->error('Request failed', [
                'request_id' => $requestId,
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
                'latency_ms' => round($latency, 2),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function getMetrics(): array
    {
        $avgLatency = $this->metrics['total_requests'] > 0
            ? $this->metrics['total_latency_ms'] / $this->metrics['total_requests']
            : 0;

        return [
            'uptime_seconds' => round($this->metrics['uptime_seconds'], 2),
            'total_requests' => $this->metrics['total_requests'],
            'average_latency_ms' => round($avgLatency, 2),
            'total_latency_ms' => round($this->metrics['total_latency_ms'], 2),
        ];
    }

    private function generateRequestId(): string
    {
        return sprintf(
            '%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2))
        );
    }

    private function getSafeHeaders(ServerRequestInterface $request): array
    {
        $headers = $request->getHeaders();

        // Remove sensitive headers
        unset(
            $headers['authorization'],
            $headers['cookie'],
            $headers['x-api-key'],
            $headers['x-auth-token']
        );

        // Truncate long headers
        foreach ($headers as $name => $values) {
            if (is_array($values)) {
                $headers[$name] = array_map(function ($value) {
                    return strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
                }, $values);
            }
        }

        return $headers;
    }
}