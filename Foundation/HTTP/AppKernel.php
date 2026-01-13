<?php

declare(strict_types=1);

namespace Avax\HTTP;

use Avax\Auth\Application\Service\RateLimiterService;
use Avax\Auth\Session\Shared\Contracts\SessionInterface;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\IpRestrictionMiddleware;
use Avax\HTTP\Middleware\MiddlewareInterface;
use Avax\HTTP\Middleware\MiddlewareRegistry;
use Avax\HTTP\Middleware\RateLimiterMiddleware;
use Avax\HTTP\Middleware\RequestLoggerMiddleware;
use Avax\HTTP\Middleware\SessionLifecycleMiddleware;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Application Kernel - Complete HTTP Runtime
 *
 * Combines Router, Kernel, and PSR-15 middleware pipeline into
 * a production-ready HTTP application runtime.
 *
 * Features:
 * - Router integration with route resolution
 * - PSR-15 middleware pipeline with global middleware
 * - Controller dispatching with DI
 * - Centralized exception handling
 * - Enterprise-grade error responses
 */
final readonly class AppKernel implements Kernel
{
    private HttpKernel $kernel;

    private RouterInterface $router;

    private ControllerDispatcher $dispatcher;

    private ResponseFactory $responseFactory;

    private array $globalMiddleware;

    /**
     * Create AppKernel with all dependencies.
     *
     * @param RouterInterface       $router           The route resolver
     * @param ControllerDispatcher  $dispatcher       The controller executor
     * @param ResponseFactory       $responseFactory  For creating responses
     * @param MiddlewareInterface[] $globalMiddleware Always-executed middleware
     *
     * @throws \ReflectionException
     */
    public function __construct(
        RouterInterface      $router,
        ControllerDispatcher $dispatcher,
        ResponseFactory      $responseFactory,
        array                $globalMiddleware = []
    )
    {
        $this->router          = $router;
        $this->dispatcher      = $dispatcher;
        $this->responseFactory = $responseFactory;

        // Default global middleware if none provided
        $this->globalMiddleware = empty($globalMiddleware)
            ? $this->createDefaultMiddlewareStack(responseFactory: $responseFactory)
            : $globalMiddleware;

        $this->kernel = new HttpKernel(
            router          : $router,
            dispatcher      : $dispatcher,
            globalMiddleware: $this->globalMiddleware,
            responseFactory : $responseFactory
        );
    }

    /**
     * Create default middleware stack for typical web applications.
     *
     * This provides sensible defaults that can be customized or replaced.
     * Order matters for security and functionality.
     *
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    private function createDefaultMiddlewareStack(ResponseFactory $responseFactory) : array
    {
        $middleware = [];

        // 1. Security first (IP restrictions, CSRF)
        if (MiddlewareRegistry::has(identifier: 'ip-restrict')) {
            $middleware[] = $this->createOfficeIpRestriction(responseFactory: $responseFactory);
        }

        // 2. Session management
        if (MiddlewareRegistry::has(identifier: 'session')) {
            $middleware[] = $this->createSessionMiddleware();
        }

        // 3. Request processing (logging, CORS)
        if (MiddlewareRegistry::has(identifier: 'log')) {
            $middleware[] = $this->createRequestLogger();
        }

        if (MiddlewareRegistry::has(identifier: 'cors')) {
            $middleware[] = MiddlewareRegistry::create(identifier: 'cors', args: [$responseFactory]);
        }

        // 4. Rate limiting (after basic processing)
        if (MiddlewareRegistry::has(identifier: 'rate-limit')) {
            $middleware[] = $this->createRateLimiter(responseFactory: $responseFactory);
        }

        // 5. Response formatting (last)
        if (MiddlewareRegistry::has(identifier: 'json')) {
            $middleware[] = MiddlewareRegistry::create(identifier: 'json', args: [$responseFactory]);
        }

        return $middleware;
    }

    /**
     * Create office IP restriction middleware.
     * In real implementation, this would be configurable.
     */
    private function createOfficeIpRestriction(ResponseFactory $responseFactory) : MiddlewareInterface
    {
        return new class($responseFactory) extends IpRestrictionMiddleware {
            protected function isAllowedIp(string $ipAddress) : bool
            {
                // Example: Allow local development and office IPs
                $allowed = ['127.0.0.1', '::1', '192.168.1.0/24'];
                foreach ($allowed as $allowedIp) {
                    if (str_contains($allowedIp, '/')) {
                        // CIDR notation - simplified check
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

    /**
     * Create session lifecycle middleware.
     */
    private function createSessionMiddleware() : MiddlewareInterface
    {
        // In real implementation, this would inject SessionInterface
        // For now, create with mock/null dependencies
        return new SessionLifecycleMiddleware(
            session: new class implements SessionInterface {
                public function start() : void {}

                public function getId() : string
                {
                    return 'mock';
                }

                public function has(string $key) : bool
                {
                    return false;
                }

                public function get(string $key, mixed $default = null) : mixed
                {
                    return $default;
                }

                public function set(string $key, mixed $value) : void {}

                public function remove(string $key) : void {}

                public function clear() : void {}

                public function destroy() : void {}

                public function regenerateId() : void {}

                public function isStarted() : bool
                {
                    return true;
                }
            }
        );
    }

    /**
     * Create request logger middleware.
     */
    private function createRequestLogger() : MiddlewareInterface
    {
        // In real implementation, this would inject PSR-3 LoggerInterface
        return new RequestLoggerMiddleware(
            logger: new class implements LoggerInterface {
                public function emergency(Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }

                public function alert(Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }

                public function critical(Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }

                public function error(Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }

                public function warning(Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }

                public function notice(Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }

                public function info(Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }

                public function debug(Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }

                public function log($level, Stringable|string $message, array $context = []) : void
                {
                    error_log($message);
                }
            }
        );
    }

    /**
     * Create rate limiter middleware.
     */
    private function createRateLimiter(ResponseFactory $responseFactory) : MiddlewareInterface
    {
        // In real implementation, this would inject RateLimiterService
        return new RateLimiterMiddleware(
            rateLimiterService: new class implements RateLimiterService {
                public function canAttempt(string $key, int $maxAttempts, int $decaySeconds) : bool
                {
                    return true;
                }

                public function recordFailedAttempt(string $key, int $maxAttempts, int $decaySeconds) : void {}

                public function remainingAttempts(string $key, int $maxAttempts, int $decaySeconds) : int
                {
                    return 60;
                }

                public function availableIn(string $key, int $maxAttempts, int $decaySeconds) : int
                {
                    return 0;
                }

                public function clear(string $key) : void {}
            },
            responseFactory   : $responseFactory,
            identifierType    : 'ip',
            maxRequests       : 100, // max requests
            timeWindow        : 60   // time window
        );
    }

    /**
     * Get middleware priority hints for configuration.
     */
    public static function getMiddlewarePriorityHints() : array
    {
        return MiddlewareRegistry::getPriorityHints();
    }

    /**
     * Process HTTP request through complete application pipeline.
     *
     * Pipeline:
     * 1. Global middleware (CORS, security headers, logging)
     * 2. Route resolution (RouterInterface)
     * 3. Route-specific middleware (if configured)
     * 4. Controller execution (ControllerDispatcher)
     * 5. Response formatting
     *
     * @param ServerRequestInterface $request The HTTP request
     *
     * @return ResponseInterface The HTTP response
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->kernel->handle(request: $request);
    }

    /**
     * Get the router for route management.
     */
    public function getRouter() : RouterInterface
    {
        return $this->router;
    }

    /**
     * Add middleware to the global pipeline.
     */
    public function withMiddleware(MiddlewareInterface $middleware) : self
    {
        return new self(
            router          : $this->router,
            dispatcher      : $this->dispatcher,
            responseFactory : $this->responseFactory,
            globalMiddleware: [...$this->globalMiddleware, $middleware]
        );
    }
}
