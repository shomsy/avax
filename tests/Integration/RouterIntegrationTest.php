<?php

declare(strict_types=1);

namespace Avax\Tests\Integration;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Avax\Components\HTTP\Request\System\Configuration\Builders\RequestBuilder;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Avax\Components\HTTP\Router\System\Capabilities\ErrorResponseBuilding\BuildErrorResponse;
use Avax\Components\HTTP\Router\System\Capabilities\MiddlewarePipeline\BuildPipeline;
use Avax\Components\HTTP\Router\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteExecution\InvokeRouteAction;
use Avax\Components\HTTP\Router\System\Capabilities\UrlBuilding\SubstituteRouteParameters;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

final class RouterIntegrationTest extends TestCase
{
    private RouterRuntimeInterface $router;

    private Responses $responses;

    public function test_get_root_route_returns_200_with_correct_body_and_headers(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Router is Working!', (string) $response->getBody());
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertStringContainsString('text/plain', $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_get_health_route_returns_ok_body(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/health'));

        self::assertSame('ok', (string) $response->getBody());
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertStringContainsString('text/plain', $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_get_test_route_returns_enterprise_router_message(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/test'));

        self::assertStringContainsString(
            'Enterprise Router Active!',
            (string) $response->getBody(),
        );
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertStringContainsString('text/plain', $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_get_nonexistent_route_returns_404() : void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/missing'));

        self::assertSame(404, $response->getStatusCode());
    }

    public function test_post_to_get_only_route_returns_405() : void
    {
        $response = $this->router->resolve(request: $this->createRequest(method: 'POST', path: '/health'));

        self::assertSame(405, $response->getStatusCode());
        self::assertStringContainsString('GET', $response->getHeaderLine('Allow'));
    }

    public function test_favicon_route_returns_204_no_content(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/favicon.ico'));

        self::assertSame(204, $response->getStatusCode());
        self::assertStringContainsString('image/x-icon', $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_all_responses_implement_response_interface(): void
    {
        foreach (['/', '/health', '/test', '/favicon.ico'] as $route) {
            $response = $this->router->resolve(request: $this->createRequest(path: $route));

            self::assertInstanceOf(
                ResponseInterface::class,
                $response,
                "Route {$route} did not return a ResponseInterface",
            );
        }
    }

    public function test_all_responses_have_content_type_header(): void
    {
        foreach (['/', '/health', '/test'] as $route) {
            $response = $this->router->resolve(request: $this->createRequest(path: $route));

            self::assertNotSame(
                '',
                $response->getHeaderLine(name: 'Content-Type'),
                "Route {$route} missing Content-Type header",
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->responses = new Responses();

        $resolveCallable = new ResolveCallable();
        $router          = new Router(
            routeCollection   : new RouteCollection(),
            matchRoute        : new MatchRoute(),
            pipelineBuilder   : new BuildPipeline($resolveCallable),
            errorResponse     : new BuildErrorResponse(),
            urlBuilder        : new SubstituteRouteParameters(),
            invokeRouteAction : new InvokeRouteAction(new NormalizeControllerResult()),
        );
        $router->get(path: '/', action: fn () => $this->textResponse(body: 'Router is Working!'));
        $router->get(path: '/health', action: fn () => $this->textResponse(body: 'ok'));
        $router->get(path: '/test', action: fn () => $this->textResponse(body: 'Enterprise Router Active!'));
        $router->get(
            path: '/favicon.ico',
            action: fn () => $this->responses->createResponseWithBody(
                content: '',
                status : 204,
                headers: ['content-type' => ['image/x-icon']],
            ),
        );

        $this->router = $router;
    }

    private function createRequest(string $method = 'GET', string $path = '/'): RequestInterface
    {
        return (new RequestBuilder())
            ->withMethod(method: $method)
            ->withUri(requestUri: new RequestUri(scheme: 'http', host: 'localhost', path: $path))
            ->build();
    }

    private function textResponse(string $body, int $statusCode = 200): ResponseInterface
    {
        return $this->responses->send(
            data  : $body,
            status: $statusCode,
        );
    }
}
