<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * PSR-15 Middleware to log details of incoming HTTP requests.
 *
 * This middleware logs the HTTP method, URI, and client IP of each incoming request
 * using the provided PSR-3 compliant logger.
 *
 * The "readonly" modifier is used to enforce immutability, ensuring that once
 * instantiated the properties cannot be altered.
 */
readonly class RequestLoggerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger Instance of the logger used to log request details.
     */
    public function __construct(LoggerInterface $logger) { $this->logger = $logger; }

    /**
     * PSR-15 process method: log incoming request details.
     *
     * This method logs information about the incoming request, including its HTTP method,
     * URI, and client IP address. After logging, it passes the request to the next handler.
     *
     * @param RequestInterface        $request The incoming request.
     * @param RequestHandlerInterface $handler The next handler in the chain.
     *
     * @return ResponseInterface Response from the next handler.
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        // Extract client IP from server parameters (PSR-7 compatible)
        $clientIp     = 'unknown';
        $serverParams = method_exists($request, 'getServerParams') ? $request->getServerParams() : [];
        $forwardedFor = (string) ($serverParams['HTTP_X_FORWARDED_FOR'] ?? '');
        $clientIp     = $serverParams['REMOTE_ADDR']
            ?? ($forwardedFor !== '' ? trim(string: explode(separator: ',', string: $forwardedFor)[0]) : null)
            ?? $serverParams['HTTP_X_REAL_IP']
            ?? 'unknown';

        // Log the request details: method, URI, and client IP.
        $this->logger->info(message: 'Incoming request', context: [
            'method' => $request->getMethod(),
            'uri'    => (string) $request->getUri(),
            'ip'     => $clientIp,
        ]);

        // Proceed to the next handler.
        return $handler->handle(request: $request);
    }
}
