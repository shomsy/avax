<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Response\ResponseFactory;
use DateMalformedStringException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-15 Middleware that enforces rate limiting per client identifier (e.g., IP address) to
 * prevent excessive requests within a defined time window.
 */
readonly class RateLimiterMiddleware implements MiddlewareInterface
{
    private const string DEFAULT_IDENTIFIER_TYPE = 'ip';

    private const int    DEFAULT_MAX_REQUESTS = 60;

    private const int    DEFAULT_TIME_WINDOW = 60;
    private int                  $timeWindow;
    private int                  $maxRequests;
    private string               $identifierType;
    private ResponseFactory      $responseFactory;
    private RateLimiterInterface $rateLimiterService;

    public function __construct(
        RateLimiterInterface $rateLimiterService,
        ResponseFactory      $responseFactory,
        string|null          $identifierType = null,
        int|null             $maxRequests = null,
        int                  $timeWindow = self::DEFAULT_TIME_WINDOW
    )
    {
        $identifierType           ??= self::DEFAULT_IDENTIFIER_TYPE;
        $maxRequests              ??= self::DEFAULT_MAX_REQUESTS;
        $this->rateLimiterService = $rateLimiterService;
        $this->responseFactory    = $responseFactory;
        $this->identifierType     = $identifierType;
        $this->maxRequests        = $maxRequests;
        $this->timeWindow         = $timeWindow;
    }

    /**
     * PSR-15 process method: apply rate limiting before proceeding.
     *
     * @param RequestInterface        $request The incoming HTTP request.
     * @param RequestHandlerInterface $handler The next handler in the chain.
     *
     * @return ResponseInterface The processed response or a rate-limit-exceeded response.
     *
     * @throws InvalidArgumentException|DateMalformedStringException If the cache is unavailable or invalid.
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
            $serverParams = method_exists($request, 'getServerParams') ? $request->getServerParams() : [];
            $forwardedFor = (string) ($serverParams['HTTP_X_FORWARDED_FOR'] ?? '');
            if ($forwardedFor !== '') {
                return trim(string: explode(separator: ',', string: $forwardedFor)[0]);
            }

            return $serverParams['REMOTE_ADDR'] ?? $serverParams['HTTP_X_REAL_IP'] ?? 'unknown';
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
     * @throws InvalidArgumentException
     */
    private function isRateLimitExceeded(string $identifier) : bool
    {
        return ! $this->rateLimiterService->canAttempt(
            key         : $identifier,
            maxAttempts : $this->maxRequests,
            decaySeconds: $this->timeWindow
        );
    }

    /**
     * Creates a response to indicate the rate limit has been exceeded.
     *
     * @return ResponseInterface The response indicating rate limit exceeded.
     */
    private function createRateLimitExceededResponse() : ResponseInterface
    {
        return $this->responseFactory
            ->createErrorResponse(statusCode: 429, message: 'Too Many Requests')
            ->withHeader(name: 'Retry-After', value: (string) $this->timeWindow);
    }
}
