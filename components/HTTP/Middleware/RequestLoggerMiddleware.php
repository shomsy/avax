<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Logs incoming HTTP requests.
 */
final readonly class RequestLoggerMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri();
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '-';

        $this->logger->info(sprintf('[HTTP] %s %s from %s', $method, $uri, $ip));

        $start = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $start) * 1000, 2);

        $this->logger->info(sprintf('[HTTP] %s %s -> %s (%sms)', $method, $uri, $response->getStatusCode(), $duration));

        return $response;
    }
}
