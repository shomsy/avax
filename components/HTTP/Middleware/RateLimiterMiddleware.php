<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

/**
 * Rate limiting middleware.
 */
final readonly class RateLimiterMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RateLimiterInterface $rateLimiter,
        private object $responseFactory,
        private string $keySource = 'ip',
        private int $maxAttempts = 60,
        private int $decaySeconds = 60,
    ) {
    }

    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $key = match ($this->keySource) {
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0',
            default => 'global',
        };

        if (! $this->rateLimiter->canAttempt($key, $this->maxAttempts, $this->decaySeconds)) {
            $remaining = $this->rateLimiter->availableIn($key, $this->maxAttempts, $this->decaySeconds);

            if (method_exists($this->responseFactory, 'rateLimited')) {
                return $this->responseFactory->rateLimited($remaining);
            }

            return new readonly class ($remaining) implements ResponseInterface {
                public function __construct(private int $retryAfter)
                {
                }

                public function getStatusCode(): int
                {
                    return 429;
                }

                public function withStatus(int $code, string $reasonPhrase = ''): self
                {
                    return $this;
                }

                public function getReasonPhrase(): string
                {
                    return 'Too Many Requests';
                }

                public function getProtocolVersion(): string
                {
                    return '1.1';
                }

                public function withProtocolVersion(string $version): self
                {
                    return $this;
                }

                public function getHeaders(): array
                {
                    return [
                        'Content-Type' => ['application/json'],
                        'Retry-After' => [(string) $this->retryAfter],
                    ];
                }

                public function hasHeader(string $name): bool
                {
                    return isset($this->getHeaders()[$name]);
                }

                public function getHeader(string $name): array
                {
                    return $this->getHeaders()[$name] ?? [];
                }

                public function getHeaderLine(string $name): string
                {
                    return implode(', ', $this->getHeader($name));
                }

                public function withHeader(string $name, $value): self
                {
                    return $this;
                }

                public function withAddedHeader(string $name, $value): self
                {
                    return $this;
                }

                public function withoutHeader(string $name): self
                {
                    return $this;
                }

                public function getBody(): mixed
                {
                    return json_encode(['message' => 'Too Many Requests', 'retry_after' => $this->retryAfter]);
                }

                public function withBody(mixed $body): self
                {
                    return $this;
                }
            };
        }

        $this->rateLimiter->recordFailedAttempt($key, $this->maxAttempts, $this->decaySeconds);

        return $next($request);
    }
}
