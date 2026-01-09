<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\Auth\Application\Service\RateLimiterService;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\ResponseFactory;
use Closure;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware that enforces rate limiting per client identifier (e.g., IP address) to
 * prevent excessive requests within a defined time window.
 */
readonly class RateLimiterMiddleware
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
     * Handles the incoming request, applying rate limiting logic based on a unique identifier.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next    The next middleware or handler.
     *
     * @return ResponseInterface The processed response or a rate-limit-exceeded response.
     *
     * @throws \Psr\Cache\InvalidArgumentException|\DateMalformedStringException If the cache is unavailable or invalid.
     */
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        $identifier = $this->extractIdentifier(request: $request);

        // Apply custom limits by overriding RateLimiterService's default values
        if ($this->isRateLimitExceeded(identifier: $identifier)) {
            return $this->createRateLimitExceededResponse();
        }

        $response = $next($request);

        // Record each attempt after handling to avoid affecting response time
        $this->rateLimiterService->recordFailedAttempt($identifier, $this->maxRequests, $this->timeWindow);

        return $response;
    }

    /**
     * Extracts a unique identifier for rate limiting (e.g., client IP or default).
     *
     * @param Request $request The current request.
     *
     * @return string The extracted identifier.
     */
    private function extractIdentifier(Request $request) : string
    {
        return $this->identifierType === 'ip' ? $request->getClientIp() : 'default';
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
