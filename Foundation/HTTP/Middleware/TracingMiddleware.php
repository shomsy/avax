<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
        $this->startTime       = microtime(as_float: true);
    }

    /**
     * @throws Throwable
     * @throws RandomException
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $requestId = $this->generateRequestId();
        $startTime = microtime(as_float: true);

        // Add request ID to request
        $request = $request->withHeader(name: $this->requestIdHeader, value: $requestId);

        $this->logger->info(message: 'Request started', context: [
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'path'   => $request->getUri()->getPath(),
            'query'  => $request->getUri()->getQuery(),
            'headers'    => $this->getSafeHeaders(request: $request),
        ]);

        try {
            $response = $handler->handle(request: $request);

            $latency = (microtime(as_float: true) - $startTime) * 1000; // ms

            // Update metrics
            $this->metrics['total_requests']++;
            $this->metrics['total_latency_ms'] += $latency;
            $this->metrics['uptime_seconds']   = microtime(as_float: true) - $this->startTime;

            $this->logger->info(message: 'Request completed', context: [
                'request_id'          => $requestId,
                'method' => $request->getMethod(),
                'path'   => $request->getUri()->getPath(),
                'status'              => $response->getStatusCode(),
                'latency_ms'          => round(num: $latency, precision: 2),
                'response_size_bytes' => strlen(string: (string) $response->getBody()),
            ]);

            // Add request ID to response
            return $response->withHeader(header: $this->requestIdHeader, value: $requestId);

        } catch (Throwable $e) {
            $latency = (microtime(as_float: true) - $startTime) * 1000;

            $this->logger->error(message: 'Request failed', context: [
                'request_id' => $requestId,
                'method' => $request->getMethod(),
                'path'   => $request->getUri()->getPath(),
                'latency_ms' => round(num: $latency, precision: 2),
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
            bin2hex(string: random_bytes(length: 4)),
            bin2hex(string: random_bytes(length: 2)),
            bin2hex(string: random_bytes(length: 2))
        );
    }

    private function getSafeHeaders(RequestInterface $request) : array
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
            if (is_array(value: $values)) {
                $headers[$name] = array_map(callback: static function ($value) {
                    return strlen(string: $value) > 100 ? substr(string: $value, offset: 0, length: 100) . '...' : $value;
                },                          array   : $values);
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
            'uptime_seconds'     => round(num: $this->metrics['uptime_seconds'], precision: 2),
            'total_requests'     => $this->metrics['total_requests'],
            'average_latency_ms' => round(num: $avgLatency, precision: 2),
            'total_latency_ms'   => round(num: $this->metrics['total_latency_ms'], precision: 2),
        ];
    }
}
