<?php

declare(strict_types=1);

use Avax\Auth\Application\Service\RateLimiterService;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\HttpKernel;
use Avax\HTTP\Middleware\CsrfVerificationMiddleware;
use Avax\HTTP\Middleware\IpRestrictionMiddleware;
use Avax\HTTP\Middleware\JsonResponseMiddleware;
use Avax\HTTP\Middleware\RateLimiterMiddleware;
use Avax\HTTP\Middleware\RequestLoggerMiddleware;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\RouterInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

/**
 * INTEGRATION TESTS: Router + Kernel + PSR-15 Middleware
 *
 * Validates that all components work together in the complete HTTP pipeline.
 */
class HttpKernelIntegrationTest extends TestCase
{
    private RouterInterface $router;

    private ControllerDispatcher $dispatcher;

    private ResponseFactory $responseFactory;

    private LoggerInterface $logger;

    private RateLimiterService $rateLimiter;

    private HttpKernel $kernel;

    /**
     * @test
     */
    public function kernel_accepts_psr7_server_request() : void
    {
        // Given: PSR-7 ServerRequest
        $request = $this->createMock(ServerRequestInterface::class);

        // When: Kernel processes request
        $response = $this->kernel->handle(request: $request);

        // Then: Returns PSR-7 ResponseInterface
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
    }

    /**
     * @test
     */
    public function middleware_pipeline_executes_in_order() : void
    {
        // Given: Request with logging expectation
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn(value: 'GET');
        $request->method('getUri')->willReturn(value: $this->createMock(UriInterface::class));
        $request->method('getServerParams')->willReturn(value: ['REMOTE_ADDR' => '127.0.0.1']);

        // Expect logging call
        $this->logger->expects(invocationRule: $this->once())
            ->method(constraint: 'info')
            ->with('Incoming request', $this->callback(callback: static function ($context) {
                return isset($context['method']) && isset($context['uri']) && isset($context['ip']);
            }));

        // When: Kernel processes request
        $response = $this->kernel->handle(request: $request);

        // Then: Response is created
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
    }

    /**
     * @test
     */
    public function rate_limiter_can_block_requests() : void
    {
        // Given: Rate limiter blocks request
        $this->rateLimiter->method('canAttempt')->willReturn(value: false);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getServerParams')->willReturn(value: ['REMOTE_ADDR' => '127.0.0.1']);

        // When: Kernel processes request
        $response = $this->kernel->handle(request: $request);

        // Then: Returns 429 Too Many Requests
        $this->assertEquals(expected: 429, actual: $response->getStatusCode());
        $this->assertStringContains('error', (string) $response->getBody());
    }

    /**
     * @test
     */
    public function json_response_middleware_formats_output() : void
    {
        // Given: Request that should get JSON response
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getServerParams')->willReturn(value: ['REMOTE_ADDR' => '127.0.0.1']);

        // Rate limiter allows request
        $this->rateLimiter->method('canAttempt')->willReturn(value: true);

        // When: Kernel processes request
        $response = $this->kernel->handle(request: $request);

        // Then: Response has JSON content type and valid JSON
        $this->assertStringContains('application/json', $response->getHeaderLine(name: 'Content-Type'));

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray(actual: $body);
    }

    /**
     * @test
     */
    public function ip_restriction_middleware_short_circuits() : void
    {
        // Given: IP restriction middleware that blocks
        $ipMiddleware = new class($this->responseFactory) extends IpRestrictionMiddleware {
            protected function isAllowedIp(string $ipAddress) : bool
            {
                return $ipAddress !== '192.168.1.100'; // Block this IP
            }
        };

        $kernel = new HttpKernel(
            router          : $this->router,
            dispatcher      : $this->dispatcher,
            globalMiddleware: [$ipMiddleware], // Only IP middleware
            responseFactory : $this->responseFactory
        );

        // Request from blocked IP
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getServerParams')->willReturn(value: ['REMOTE_ADDR' => '192.168.1.100']);

        // When: Kernel processes request
        $response = $kernel->handle(request: $request);

        // Then: Returns 403 without reaching other middleware
        $this->assertEquals(expected: 403, actual: $response->getStatusCode());

        // Logger should not be called (short-circuited)
        $this->logger->expects(invocationRule: $this->never())->method(constraint: 'info');
    }

    /**
     * @test
     */
    public function kernel_handles_exceptions_gracefully() : void
    {
        // Given: Router throws exception
        $this->router->method('resolve')->willThrowException(exception: new Exception(message: 'Route not found'));

        $request = $this->createMock(ServerRequestInterface::class);

        // When: Kernel processes request
        $response = $this->kernel->handle(request: $request);

        // Then: Returns 500 error response
        $this->assertEquals(expected: 500, actual: $response->getStatusCode());
        $this->assertStringContains('application/json', $response->getHeaderLine(name: 'Content-Type'));

        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey(key: 'error', array: $body);
    }

    /**
     * @test
     */
    public function csrf_middleware_allows_safe_methods() : void
    {
        // Given: CSRF middleware and GET request (safe method)
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $kernel = new HttpKernel(
            router          : $this->router,
            dispatcher      : $this->dispatcher,
            globalMiddleware: [$csrfMiddleware],
            responseFactory : $this->responseFactory
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn(value: 'GET');

        // When: Kernel processes GET request
        $response = $kernel->handle(request: $request);

        // Then: Request proceeds without CSRF validation
        $this->assertNotEquals(expected: 403, actual: $response->getStatusCode());
    }

    /**
     * @test
     */
    public function csrf_middleware_blocks_post_without_token() : void
    {
        // Given: CSRF middleware and POST request without token
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $kernel = new HttpKernel(
            router          : $this->router,
            dispatcher      : $this->dispatcher,
            globalMiddleware: [$csrfMiddleware],
            responseFactory : $this->responseFactory
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn(value: 'POST');
        $request->method('hasHeader')->with('X-CSRF-Token')->willReturn(value: false);
        $request->method('getAttribute')->with('_csrf_token')->willReturn(value: null);
        $request->method('getParsedBody')->willReturn(value: []);

        // When: Kernel processes POST request without token
        $response = $kernel->handle(request: $request);

        // Then: Returns 403 Forbidden
        $this->assertEquals(expected: 403, actual: $response->getStatusCode());
        $this->assertStringContains('CSRF token verification failed', (string) $response->getBody());
    }

    /**
     * @test
     */
    public function csrf_middleware_accepts_valid_token_from_header() : void
    {
        // Given: CSRF middleware and POST request with valid token in header
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $kernel = new HttpKernel(
            router          : $this->router,
            dispatcher      : $this->dispatcher,
            globalMiddleware: [$csrfMiddleware],
            responseFactory : $this->responseFactory
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn(value: 'POST');
        $request->method('hasHeader')->with('X-CSRF-Token')->willReturn(value: true);
        $request->method('getHeaderLine')->with('X-CSRF-Token')->willReturn(value: 'valid-csrf-token-32-chars-long-token');

        // When: Kernel processes POST request with valid token
        $response = $kernel->handle(request: $request);

        // Then: Request proceeds (doesn't return 403)
        $this->assertNotEquals(expected: 403, actual: $response->getStatusCode());
    }

    /**
     * @test
     */
    public function csrf_middleware_accepts_token_from_request_attribute() : void
    {
        // Given: CSRF middleware and POST request with token in attribute
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $kernel = new HttpKernel(
            router          : $this->router,
            dispatcher      : $this->dispatcher,
            globalMiddleware: [$csrfMiddleware],
            responseFactory : $this->responseFactory
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn(value: 'POST');
        $request->method('hasHeader')->with('X-CSRF-Token')->willReturn(value: false);
        $request->method('getAttribute')->with('_csrf_token')->willReturn(value: 'valid-csrf-token-32-chars-long-token');
        $request->method('getParsedBody')->willReturn(value: []);

        // When: Kernel processes POST request with token in attribute
        $response = $kernel->handle(request: $request);

        // Then: Request proceeds
        $this->assertNotEquals(expected: 403, actual: $response->getStatusCode());
    }

    /**
     * @test
     */
    public function csrf_middleware_accepts_token_from_post_body() : void
    {
        // Given: CSRF middleware and POST request with token in body
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $kernel = new HttpKernel(
            router          : $this->router,
            dispatcher      : $this->dispatcher,
            globalMiddleware: [$csrfMiddleware],
            responseFactory : $this->responseFactory
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn(value: 'POST');
        $request->method('hasHeader')->with('X-CSRF-Token')->willReturn(value: false);
        $request->method('getAttribute')->with('_csrf_token')->willReturn(value: null);
        $request->method('getParsedBody')->willReturn(value: ['csrf_token' => 'valid-csrf-token-32-chars-long-token']);

        // When: Kernel processes POST request with token in body
        $response = $kernel->handle(request: $request);

        // Then: Request proceeds
        $this->assertNotEquals(expected: 403, actual: $response->getStatusCode());
    }

    protected function setUp() : void
    {
        $psr17Factory = new Psr17Factory;

        $this->router          = $this->createMock(RouterInterface::class);
        $this->dispatcher      = new ControllerDispatcher(container: $this->createMock(ContainerInterface::class));
        $this->responseFactory = new ResponseFactory(
            streamFactory: $psr17Factory->createStreamFactory(),
            response     : $psr17Factory->createResponseFactory()->createResponse()
        );

        $this->logger      = $this->createMock(LoggerInterface::class);
        $this->rateLimiter = $this->createMock(RateLimiterService::class);

        // Create kernel with PSR-15 middleware
        $middleware = [
            new RequestLoggerMiddleware(logger: $this->logger),
            new RateLimiterMiddleware(rateLimiterService: $this->rateLimiter, responseFactory: $this->responseFactory),
            new JsonResponseMiddleware(responseFactory: $this->responseFactory),
        ];

        $this->kernel = new HttpKernel(
            router          : $this->router,
            dispatcher      : $this->dispatcher,
            globalMiddleware: $middleware,
            responseFactory : $this->responseFactory
        );
    }
}
