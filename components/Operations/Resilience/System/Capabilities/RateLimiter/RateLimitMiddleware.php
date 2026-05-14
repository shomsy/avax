<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter;

use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class RateLimitMiddleware
{
    public function __construct(
        private RedisRateLimiter $redisRateLimiter,
        private ResponseFactory  $responseFactory,
        private array $config = [],
    ) {
    }

    public function process(ServerRequestInterface $serverRequest, object $handler): ResponseInterface
    {
        return $this->handle(
            next   : static fn (ServerRequestInterface $serverRequest): ResponseInterface => $handler->handle($serverRequest),
            request: $serverRequest,
        );
    }

    public function handle(ServerRequestInterface $serverRequest, Closure $next): ResponseInterface
    {
        $decision = $this->decision(request: $serverRequest);

        if (! $decision->allowed) {
            return $this->withRateLimitHeaders(
                response: $this->responseFactory->json(
                    data      : [
                        'message' => 'Too Many Requests',
                        'retry_after' => $decision->retryAfter,
                    ],
                    statusCode: 429,
                ),
                decision: $decision,
            );
        }

        return $this->withRateLimitHeaders(
            response: $next($serverRequest),
            decision: $decision,
        );
    }

    private function decision(ServerRequestInterface $serverRequest): RateLimitDecision
    {
        return new RateLimiter(redisRateLimiter: $this->redisRateLimiter)->attempt(
            key         : $this->keyFor(request: $serverRequest),
            maxAttempts : $this->config['max_attempts'] ?? 60,
            decaySeconds: $this->config['decay_seconds'] ?? 60,
        );
    }

    private function keyFor(ServerRequestInterface $serverRequest): string
    {
        $server = $serverRequest->getServerParams();
        $parts = [];

        foreach ($this->config['key_by'] ?? ['ip', 'path'] as $segment) {
            $parts[] = match ($segment) {
                'user' => (string) ($serverRequest->getAttribute('user_id') ?? 'guest'),
                'path' => $serverRequest->getUri()->getPath(),
                default => (string) ($server['REMOTE_ADDR'] ?? '0.0.0.0'),
            };
        }

        return implode(':', $parts);
    }

    private function withRateLimitHeaders(ResponseInterface $response, RateLimitDecision $rateLimitDecision): ResponseInterface
    {
        foreach ($rateLimitDecision->headers() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
