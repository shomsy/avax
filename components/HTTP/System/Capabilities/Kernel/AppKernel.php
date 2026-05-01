<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Kernel;

use Avax\Components\HTTP\Middleware\IpRestrictionMiddleware;
use Avax\Components\HTTP\Middleware\MiddlewareInterface;
use Avax\Components\HTTP\Middleware\MiddlewareRegistry;
use Avax\Components\HTTP\Middleware\RateLimiterInterface;
use Avax\Components\HTTP\Middleware\RateLimiterMiddleware;
use Avax\Components\HTTP\Middleware\RequestLoggerMiddleware;
use Avax\Components\HTTP\Middleware\SessionLifecycleMiddleware;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\RouterRuntimeInterface;
use Avax\Components\HTTP\Session\NullSession;
use Avax\Components\HTTP\System\PublicSurface\HttpInterface;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Stringable;

/**
 * Application Kernel - Complete HTTP Runtime
 *
 * Combines Router, Kernel, and PSR-15 middleware pipeline into
 * a production-ready HTTP application runtime.
 */
final readonly class AppKernel implements HttpInterface, Kernel
{
    private array $middlewareStack;

    public function __construct(
        private RouterRuntimeInterface $routerRuntime,
        private ResponseFactory $responseFactory,
        private array $globalMiddleware = [],
    ) {
        $this->middlewareStack = $this->globalMiddleware === []
            ? $this->createDefaultMiddlewareStack()
            : $this->globalMiddleware;
    }

    private function createDefaultMiddlewareStack(): array
    {
        $middleware = [];

        if (MiddlewareRegistry::has('ip-restrict')) {
            $middleware[] = $this->createOfficeIpRestriction();
        }

        if (MiddlewareRegistry::has('session')) {
            $middleware[] = $this->createSessionMiddleware();
        }

        if (MiddlewareRegistry::has('log')) {
            $middleware[] = $this->createRequestLogger();
        }

        if (MiddlewareRegistry::has('cors')) {
            $middleware[] = MiddlewareRegistry::create('cors', [$this->responseFactory]);
        }

        if (MiddlewareRegistry::has('rate-limit')) {
            $middleware[] = $this->createRateLimiter();
        }

        if (MiddlewareRegistry::has('json')) {
            $middleware[] = MiddlewareRegistry::create('json', [$this->responseFactory]);
        }

        return $middleware;
    }

    private function createOfficeIpRestriction(): MiddlewareInterface
    {
        return new class () extends IpRestrictionMiddleware {
            #[Override]
            protected function isAllowedIp(#[SensitiveParameter] string $ipAddress): bool
            {
                $allowed = ['127.0.0.1', '::1', '192.168.1.0/24'];
                foreach ($allowed as $allowedIp) {
                    if (str_contains($allowedIp, '/')) {
                        if (str_starts_with($ipAddress, '192.168.1.')) {
                            return true;
                        }
                    } elseif ($ipAddress === $allowedIp) {
                        return true;
                    }
                }

                return false;
            }
        };
    }

    private function createSessionMiddleware(): MiddlewareInterface
    {
        return new SessionLifecycleMiddleware(new NullSession());
    }

    private function createRequestLogger(): MiddlewareInterface
    {
        return new RequestLoggerMiddleware(
            new class () implements LoggerInterface {
                public function emergency(Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }

                public function alert(Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }

                public function critical(Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }

                public function error(Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }

                public function warning(Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }

                public function notice(Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }

                public function info(Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }

                public function debug(Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }

                public function log($level, Stringable|string $message, array $context = []): void
                {
                    error_log((string) $message);
                }
            },
        );
    }

    private function createRateLimiter(): MiddlewareInterface
    {
        return new RateLimiterMiddleware(
            new class () implements RateLimiterInterface {
                public function canAttempt(string $key, int $maxAttempts, int $decaySeconds): bool
                {
                    return true;
                }

                public function recordFailedAttempt(string $key, int $maxAttempts, int $decaySeconds): void
                {
                }

                public function remainingAttempts(string $key, int $maxAttempts, int $decaySeconds): int
                {
                    return 60;
                }

                public function availableIn(string $key, int $maxAttempts, int $decaySeconds): int
                {
                    return 0;
                }

                public function clear(string $key): void
                {
                }
            },
            (new class () {
                public function rateLimited(int $retryAfter): ResponseInterface
                {
                    return new ResponseFactory()->rateLimited($retryAfter);
                }
            }),
            'ip',
            100,
            60,
        );
    }

    public static function getMiddlewarePriorityHints(): array
    {
        return MiddlewareRegistry::getPriorityHints();
    }

    public function handle(ServerRequestInterface $serverRequest) : ResponseInterface
    {
        if (! $serverRequest instanceof RequestInterface) {
            return $this->responseFactory->error('Invalid request type', 400);
        }

        return $this->runPipeline($serverRequest);
    }

    private function runPipeline(RequestInterface $request): ResponseInterface
    {
        $core = fn (RequestInterface $request) : ResponseInterface => $this->routerRuntime->resolve($request);

        $pipeline = $this->middlewareStack;

        while ($middleware = array_pop($pipeline)) {
            $core = static fn (RequestInterface $request) => $middleware->handle($request, $core);
        }

        return $core($request);
    }

    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }

    public function terminate(RequestInterface $request, ResponseInterface $response) : void
    {
    }

    public function getRouter(): RouterRuntimeInterface
    {
        return $this->routerRuntime;
    }

    public function withMiddleware(MiddlewareInterface $middleware): self
    {
        return new self(
            $this->routerRuntime,
            $this->responseFactory,
            [...$this->middlewareStack, $middleware],
        );
    }
}
