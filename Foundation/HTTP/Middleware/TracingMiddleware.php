<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use SensitiveParameter;
use Throwable;

class TracingMiddleware implements MiddlewareInterface
{
    private array $metrics
        = [
            'total_requests'   => 0,
            'total_latency_ms' => 0,
            'uptime_seconds'   => 0,
        ];

    private float           $startTime;
    private string          $requestIdHeader = 'X-ServerRequest-ID';
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface              $logger,
        #[SensitiveParameter] string $requestIdHeader = 'X-ServerRequest-ID'
    )
    {
        $this->logger          = $logger;
        $this->requestIdHeader = $requestIdHeader;
        $this->startTime       = microtime(true);
    }

    /**
     * @throws Throwable
     * @throws RandomException
     */
    public function process(ServerRequestInterface $request, callable $next) : ResponseInterface
    {
        $requestId = $this->generateRequestId();
        $startTime = microtime(true);

        // Add request ID to request
        $request = $request->withHeader(name: $this->requestIdHeader, value: $requestId);

        $this->logger->info(message: 'ServerRequest started', context: [
            'request_id' => $requestId,
            'method'     => $request->method,
            'path'       => $request->uri->getPath(),
            'query'      => $request->uri->getQuery(),
            'headers'    => $this->getSafeHeaders(request: $request),
        ]);

        try {
            $response = $next($request);

            $latency = (microtime(true) - $startTime) * 1000; // ms

            // Update metrics
            $this->metrics['total_requests']++;
            $this->metrics['total_latency_ms'] += $latency;
            $this->metrics['uptime_seconds']   = microtime(true) - $this->startTime;

            $this->logger->info(message: 'ServerRequest completed', context: [
                'request_id'          => $requestId,
                'method'              => $request->method,
                'path'                => $request->uri->getPath(),
                'status'              => $response->getStatusCode(),
                'latency_ms'          => round($latency, 2),
                'response_size_bytes' => strlen((string) $response->getBody()),
            ]);

            // Add request ID to response
            return $response->withHeader($this->requestIdHeader, $requestId);

        } catch (Throwable $e) {
            $latency = (microtime(true) - $startTime) * 1000;

            $this->logger->error(message: 'ServerRequest failed', context: [
                'request_id' => $requestId,
                'method'     => $request->method,
                'path'       => $request->uri->getPath(),
                'latency_ms' => round($latency, 2),
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * @throws RandomException
     */
    private function generateRequestId() : string
    {
        return sprintf(
            '%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2))
        );
    }

    private function getSafeHeaders(ServerRequestInterface $request) : array
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

    public function getMetrics() : array
    {
        $avgLatency = $this->metrics['total_requests'] > 0
            ? $this->metrics['total_latency_ms'] / $this->metrics['total_requests']
            : 0;

        return [
            'uptime_seconds'     => round($this->metrics['uptime_seconds'], 2),
            'total_requests'     => $this->metrics['total_requests'],
            'average_latency_ms' => round($avgLatency, 2),
            'total_latency_ms'   => round($this->metrics['total_latency_ms'], 2),
        ];
    }
}