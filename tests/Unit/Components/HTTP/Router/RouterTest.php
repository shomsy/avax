<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Router;

use Avax\Components\HTTP\Request\System\Capabilities\Body\ParsedBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RawBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RequestBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\UploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\RequestHeaders;
use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Avax\Components\HTTP\Request\System\PublicSurface\Request;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;

    #[Test]
    public function exactRouteMatch() : void
    {
        $this->router->get('/hello', static fn () => 'Hello World');

        $response = $this->router->dispatch($this->createRequest('GET', '/hello'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello World', (string) $response->getBody());
    }

    private function createRequest(string $method, string $uri) : Request
    {
        $parts = explode('?', $uri, 2);
        $path  = $parts[0];
        $query = $parts[1] ?? '';

        return new Request(
            method        : $method,
            requestUri    : new RequestUri(
                                scheme: 'http',
                                host  : 'localhost',
                                path  : $path,
                                query : $query,
                            ),
            requestHeaders: new RequestHeaders([]),
            requestBody   : new RequestBody(new RawBody(''), new ParsedBody([])),
            uploadedFiles : new UploadedFiles([]),
            serverParams  : [],
            cookieParams  : [],
            queryParams   : [],
        );
    }

    // ===== Basic Route Matching =====

    #[Test]
    public function parameterizedRouteMatch() : void
    {
        $this->router->get('/users/{id}', static function (Request $request, string $id) {
            return 'User: ' . $id;
        });

        $response = $this->router->dispatch($this->createRequest('GET', '/users/42'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('User: 42', (string) $response->getBody());
    }

    #[Test]
    public function multipleParameterRouteMatch() : void
    {
        $this->router->get('/users/{userId}/posts/{postId}', static function (Request $request, string $userId, string $postId) {
            return "User {$userId}, Post {$postId}";
        });

        $response = $this->router->dispatch($this->createRequest('GET', '/users/5/posts/99'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('User 5, Post 99', (string) $response->getBody());
    }

    #[Test]
    public function postRouteMatch() : void
    {
        $this->router->post('/users', static fn () => 'Created');

        $response = $this->router->dispatch($this->createRequest('POST', '/users'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Created', (string) $response->getBody());
    }

    // ===== HTTP Methods =====

    #[Test]
    public function allHttpMethodsWork() : void
    {
        $this->router->get('/test', static fn () => 'GET');
        $this->router->post('/test', static fn () => 'POST');
        $this->router->put('/test', static fn () => 'PUT');
        $this->router->patch('/test', static fn () => 'PATCH');
        $this->router->delete('/test', static fn () => 'DELETE');
        $this->router->options('/test', static fn () => 'OPTIONS');
        $this->router->head('/test', static fn () => 'HEAD');

        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'] as $method) {
            $response = $this->router->dispatch($this->createRequest($method, '/test'));
            self::assertSame(200, $response->getStatusCode(), "Failed for method: {$method}");
            self::assertSame($method, (string) $response->getBody(), "Wrong body for method: {$method}");
        }
    }

    #[Test]
    public function anyRouteRegistersAllMethods() : void
    {
        $this->router->any('/any', static fn () => 'Any');

        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'] as $method) {
            $response = $this->router->dispatch($this->createRequest($method, '/any'));
            self::assertSame(200, $response->getStatusCode(), "Failed for method: {$method}");
        }
    }

    #[Test]
    public function missingRouteReturns404() : void
    {
        $this->router->get('/hello', static fn () => 'Hello');

        $response = $this->router->dispatch($this->createRequest('GET', '/missing'));

        self::assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        self::assertArrayHasKey('error', $body);
    }

    // ===== 404 =====

    #[Test]
    public function wrongMethodReturns405() : void
    {
        $this->router->get('/users', static fn () => 'Users');

        $response = $this->router->dispatch($this->createRequest('POST', '/users'));

        self::assertSame(405, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame('Method Not Allowed', $body['error']);
        self::assertContains('GET', $body['allowed']);
        self::assertStringContainsString('GET', $response->getHeaderLine('Allow'));
    }

    // ===== 405 Method Not Allowed =====

    #[Test]
    public function multipleAllowedMethodsIn405() : void
    {
        $this->router->get('/resource', static fn () => 'GET');
        $this->router->post('/resource', static fn () => 'POST');

        $response = $this->router->dispatch($this->createRequest('DELETE', '/resource'));

        self::assertSame(405, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        self::assertContains('GET', $body['allowed']);
        self::assertContains('POST', $body['allowed']);
    }

    #[Test]
    public function fallbackRouteExecutes() : void
    {
        $this->router->fallback(static fn () => 'Fallback');

        $response = $this->router->dispatch($this->createRequest('GET', '/anything'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Fallback', (string) $response->getBody());
    }

    // ===== Fallback Route =====

    #[Test]
    public function fallbackDoesNotHide405() : void
    {
        $this->router->get('/users', static fn () => 'Users');
        $this->router->fallback(static fn () => 'Fallback');

        $response = $this->router->dispatch($this->createRequest('POST', '/users'));

        self::assertSame(405, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame('Method Not Allowed', $body['error']);
    }

    #[Test]
    public function exactMatchWinsOverFallback() : void
    {
        $this->router->get('/hello', static fn () => 'Hello');
        $this->router->fallback(static fn () => 'Fallback');

        $response = $this->router->dispatch($this->createRequest('GET', '/hello'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello', (string) $response->getBody());
    }

    #[Test]
    public function namedRouteUrlGeneration() : void
    {
        $this->router->get('/users/{id}', static fn () => 'User')->name('user.show');

        $url = $this->router->url('user.show', ['id' => 42]);

        self::assertSame('/users/42', $url);
    }

    // ===== Named Routes & URL Generation =====

    #[Test]
    public function namedRouteWithExtraParams() : void
    {
        $this->router->get('/users/{id}', static fn () => 'User')->name('user.show');

        $url = $this->router->url('user.show', ['id' => 42, 'tab' => 'profile']);

        self::assertSame('/users/42?tab=profile', $url);
    }

    #[Test]
    public function missingUrlParameterFails() : void
    {
        $this->router->get('/users/{id}', static fn () => 'User')->name('user.show');

        $this->expectException(RouterFailure::class);
        $this->expectExceptionMessage("Missing required parameter 'id'");

        $this->router->url('user.show');
    }

    #[Test]
    public function unknownRouteNameFails() : void
    {
        $this->expectException(RouterFailure::class);
        $this->expectExceptionMessage("Route 'unknown' is not registered");

        $this->router->url('unknown');
    }

    #[Test]
    public function routeMiddlewareExecutes() : void
    {
        $middlewareCalled = false;
        $middleware       = static function ($request, $handler) use (&$middlewareCalled) {
            $middlewareCalled = true;
            $response         = $handler($request);

            return $response->withHeader('X-Middleware', 'true');
        };

        $this->router->get('/with-middleware', static fn () => 'OK')
            ->middleware($middleware);

        $response = $this->router->dispatch($this->createRequest('GET', '/with-middleware'));

        self::assertTrue($middlewareCalled);
        self::assertSame('true', $response->getHeaderLine('X-Middleware'));
    }

    // ===== Middleware =====

    #[Test]
    public function middlewareShortCircuit() : void
    {
        $handlerCalled = false;

        $middleware = static function ($request, $handler) {
            return Response::json(['blocked' => true], 403);
        };

        $this->router->get('/protected', static function () use (&$handlerCalled) {
            $handlerCalled = true;

            return 'Secret';
        })->middleware($middleware);

        $response = $this->router->dispatch($this->createRequest('GET', '/protected'));

        self::assertFalse($handlerCalled);
        self::assertSame(403, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        self::assertTrue($body['blocked']);
    }

    #[Test]
    public function multipleRouteMiddlewareExecuteInOrder() : void
    {
        $order = [];

        $mw1 = static function ($request, $handler) use (&$order) {
            $order[]  = 'mw1-before';
            $response = $handler($request);
            $order[]  = 'mw1-after';

            return $response->withHeader('X-MW1', '1');
        };

        $mw2 = static function ($request, $handler) use (&$order) {
            $order[]  = 'mw2-before';
            $response = $handler($request);
            $order[]  = 'mw2-after';

            return $response->withHeader('X-MW2', '2');
        };

        $this->router->get('/multi-mw', static fn () => 'OK')
            ->middleware([$mw1, $mw2]);

        $response = $this->router->dispatch($this->createRequest('GET', '/multi-mw'));

        self::assertSame(['mw1-before', 'mw2-before', 'mw2-after', 'mw1-after'], $order);
        self::assertSame('1', $response->getHeaderLine('X-MW1'));
        self::assertSame('2', $response->getHeaderLine('X-MW2'));
    }

    #[Test]
    public function routeGroupWithPrefix() : void
    {
        $this->router->group('/api/v1', static function (RouterInterface $api) {
            $api->get('/users', static fn () => 'API Users');
            $api->get('/posts', static fn () => 'API Posts');
        });

        $response = $this->router->dispatch($this->createRequest('GET', '/api/v1/users'));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('API Users', (string) $response->getBody());

        $response = $this->router->dispatch($this->createRequest('GET', '/api/v1/posts'));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('API Posts', (string) $response->getBody());
    }

    // ===== Route Groups =====

    #[Test]
    public function routeGroupWithMiddleware() : void
    {
        $middlewareCalled = false;
        $middleware       = static function ($request, $response) use (&$middlewareCalled) {
            $middlewareCalled = true;

            return $response;
        };

        $this->router->group('/admin', static function (RouterInterface $admin) {
            $admin->get('/dashboard', static fn () => 'Dashboard');
        },                   $middleware);

        $response = $this->router->dispatch($this->createRequest('GET', '/admin/dashboard'));

        self::assertTrue($middlewareCalled);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function nestedRouteGroups() : void
    {
        $this->router->group('/api', static function (RouterInterface $api) {
            $api->group('/v2', static function (RouterInterface $v2) {
                $v2->get('/users', static fn () => 'API V2 Users');
            });
        });

        $response = $this->router->dispatch($this->createRequest('GET', '/api/v2/users'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('API V2 Users', (string) $response->getBody());
    }

    #[Test]
    public function namedRoutesInGroups() : void
    {
        $this->router->group('/api', static function (RouterInterface $api) {
            $api->get('/users/{id}', static fn () => 'User')->name('api.user');
        });

        $url = $this->router->url('api.user', ['id' => 10]);
        self::assertSame('/api/users/10', $url);
    }

    #[Test]
    public function stringResponseNormalized() : void
    {
        $this->router->get('/text', static fn () => 'Plain text');

        $response = $this->router->dispatch($this->createRequest('GET', '/text'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Plain text', (string) $response->getBody());
    }

    // ===== Response Normalization =====

    #[Test]
    public function arrayResponseNormalizedToJson() : void
    {
        $this->router->get('/json', static fn () => ['key' => 'value']);

        $response = $this->router->dispatch($this->createRequest('GET', '/json'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame(['key' => 'value'], $body);
    }

    #[Test]
    public function headRequestWorks() : void
    {
        $this->router->head('/health', static fn () => 'OK');

        $response = $this->router->dispatch($this->createRequest('HEAD', '/health'));

        self::assertSame(200, $response->getStatusCode());
    }

    // ===== HEAD and OPTIONS behavior =====

    #[Test]
    public function optionsRequestWorks() : void
    {
        $this->router->options('/api', static fn () => 'OK');

        $response = $this->router->dispatch($this->createRequest('OPTIONS', '/api'));

        self::assertSame(200, $response->getStatusCode());
    }

    protected function setUp() : void
    {
        $this->router = new Router();
    }
}
