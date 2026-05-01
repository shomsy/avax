<?php

declare(strict_types=1);

namespace Avax\Components\Resilience\System\Capabilities\RateLimiter;

use Avax\Components\HTTP\Response\ResponseFactory;
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class RateLimitMiddleware
{
    public function __construct(
        private RedisRateLimiter $limiter = new RedisRateLimiter(),
        private ResponseFactory  $responses = new ResponseFactory(),
        private array            $config = [],
    ) {}

    public function process(ServerRequestInterface $request, object $handler) : ResponseInterface
    {
        return $this->handle(
            request: $request,
            next   : static fn (ServerRequestInterface $nextRequest) : ResponseInterface => $handler->handle($nextRequest),
        );
    }

    public function handle(ServerRequestInterface $request, Closure $next) : ResponseInterface
    {
        $decision = $this->decision(request: $request);

        if (! $decision->allowed) {
            return $this->withRateLimitHeaders(
                response: $this->responses->json(
                            data      : [
                                            'message'     => 'Too Many Requests',
                                            'retry_after' => $decision->retryAfter,
                                        ],
                            statusCode: 429,
                        ),
                decision: $decision,
            );
        }

        return $this->withRateLimitHeaders(
            response: $next($request),
            decision: $decision,
        );
    }

    private function decision(ServerRequestInterface $request) : RateLimitDecision
    {
        return (new RateLimiter(limiter: $this->limiter))->attempt(
            key         : $this->keyFor(request: $request),
            maxAttempts : $this->config['max_attempts'] ?? 60,
            decaySeconds: $this->config['decay_seconds'] ?? 60,
        );
    }

    private function keyFor(ServerRequestInterface $request) : string
    {
        $server = $request->getServerParams();
        $parts  = [];

        foreach ($this->config['key_by'] ?? ['ip', 'path'] as $segment) {
            $parts[] = match ($segment) {
                'user'  => (string) ($request->getAttribute('user_id') ?? 'guest'),
                'path'  => $request->getUri()->getPath(),
                default => (string) ($server['REMOTE_ADDR'] ?? '0.0.0.0'),
            };
        }

        return implode(':', $parts);
    }

    private function withRateLimitHeaders(ResponseInterface $response, RateLimitDecision $decision) : ResponseInterface
    {
        foreach ($decision->headers() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
