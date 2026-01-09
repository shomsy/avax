<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\HTTP\Request\Request;
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Middleware to log details of incoming HTTP requests.
 *
 * This middleware logs the HTTP method, URI, and client IP of each incoming request
 * using the provided PSR-3 compliant logger.
 *
 * The "readonly" modifier is used to enforce immutability, ensuring that once
 * instantiated the properties cannot be altered.
 */
readonly class RequestLoggerMiddleware
{
    /**
     * @param LoggerInterface $logger Instance of the logger used to log request details.
     */
    public function __construct(private LoggerInterface $logger) {}

    /**
     * Handle an incoming request and log its details.
     *
     * This method logs information about the incoming request, including its HTTP method,
     * URI, and client IP address. After logging, it passes the request to the next middleware/component.
     *
     * @param Request $request Incoming HTTP request.
     * @param Closure $next    Next middleware or handler in the request lifecycle.
     *
     * @return ResponseInterface Response from the next middleware/component.
     */
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        // Log the request details: method, URI, and client IP.
        $this->logger->info(message: 'Incoming request', context: [
            'method' => $request->getMethod(),
            'uri'    => (string) $request->getUri(),
            'ip'     => $request->getClientIp(),
        ]);

        // Proceed to the next middleware or handler.
        return $next($request);
    }
}