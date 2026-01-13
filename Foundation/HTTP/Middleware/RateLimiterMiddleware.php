<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\Auth\Application\Service\RateLimiterService;
use Avax\HTTP\Response\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR-15 Middleware that enforces rate limiting per client identifier (e.g., IP address) to
 * prevent excessive requests within a defined time window.
 */
readonly class RateLimiterMiddleware implements MiddlewareInterface
{
    private const string DEFAULT_IDENTIFIER_TYPE = 'ip';

    private const int    DEFAULT_MAX_REQUESTS = 60;

    private const int    DEFAULT_TIME_WINDOW = 60;

    public function __construct(
        private RateLimiterService $rateLimiterService,
        private ResponseFactory    $responseFactory,
        private string             $identifierType = self::DEFAULT_IDENTIFIER_TYPE,
        private int                $maxRequests = self::DEFAULT_MAX_REQUESTS,
        private int                $timeWindow = self::DEFAULT_TIME_WINDOW
    ) {}

    /**
     * PSR-15 process method: apply rate limiting before proceeding.
     *
     * @param RequestInterface        $request The incoming HTTP request.
     * @param RequestHandlerInterface $handler The next handler in the chain.
     *
     * @return ResponseInterface The processed response or a rate-limit-exceeded response.
     *
     * @throws \Psr\Cache\InvalidArgumentException|\DateMalformedStringException If the cache is unavailable or invalid.
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $identifier = $this->extractIdentifier(request: $request);

        // Apply custom limits by overriding RateLimiterService's default values
        if ($this->isRateLimitExceeded(identifier: $identifier)) {
            return $this->createRateLimitExceededResponse();
        }

        $response = $handler->handle(request: $request);

        // Record each attempt after handling to avoid affecting response time
        $this->rateLimiterService->recordFailedAttempt(key: $identifier, maxAttempts: $this->maxRequests, decaySeconds: $this->timeWindow);

        return $response;
    }

    /**
     * Extracts a unique identifier for rate limiting (e.g., client IP or default).
     *
     * @param RequestInterface $request The current request.
     *
     * @return string The extracted identifier.
     */
    private function extractIdentifier(RequestInterface $request) : string
    {
        if ($this->identifierType === 'ip') {
            // Extract IP from PSR-7 ServerRequestInterface
            if ($request instanceof ServerRequestInterface) {
                $serverParams = $request->getServerParams();

                return $serverParams['REMOTE_ADDR'] ??
                    $serverParams['HTTP_X_FORWARDED_FOR'] ??
                    $serverParams['HTTP_X_REAL_IP'] ??
                    'unknown';
            }

            return 'unknown';
        }

        return 'default';
    }

    /**
     * Checks if the rate limit has been exceeded based on the identifier.
     *
     * @param string $identifier The unique identifier for rate limiting.
     *
     * @return bool True if rate limit is exceeded, false otherwise.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function isRateLimitExceeded(string $identifier) : bool
    {
        return ! $this->rateLimiterService->canAttempt(
            identifier : $identifier,
            maxAttempts: $this->maxRequests,
            timeWindow : $this->timeWindow
        );
    }

    /**
     * Creates a response to indicate the rate limit has been exceeded.
     *
     * @return ResponseInterface The response indicating rate limit exceeded.
     */
    private function createRateLimitExceededResponse() : ResponseInterface
    {
        return $this->responseFactory->createResponse(
            code        : 429,
            reasonPhrase: 'Too Many Requests'
        )->withHeader(name: 'Retry-After', value: (string) $this->timeWindow);
    }
}
