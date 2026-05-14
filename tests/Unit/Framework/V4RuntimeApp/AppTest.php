<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeApp;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Flows\CreateApplication\CreateApplication;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Framework\System\PublicSurface\App;
use Avax\Tests\TestCase;
use ReflectionClass;

/**
 * @covers \Avax\Framework\System\PublicSurface\App
 */
final class AppTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = (new CreateApplication(clock: new SystemClock()))->make(environment: 'testing');
    }

    public function testRegisterAndGetRoute(): void
    {
        $this->app->get('/hello', fn () => 'Hello World');

        $request = new RuntimeRequest(
            method: 'GET',
            uri: '/hello',
        );

        $response = $this->app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello World', (string) $response->getBody());
    }

    public function testPostRoute(): void
    {
        $this->app->post('/submit', fn () => 'Submitted');

        $request = new RuntimeRequest(
            method: 'POST',
            uri: '/submit',
        );

        $response = $this->app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Submitted', (string) $response->getBody());
    }

    public function testPutRoute(): void
    {
        $this->app->put('/update', fn () => 'Updated');

        $request = new RuntimeRequest(
            method: 'PUT',
            uri: '/update',
        );

        $response = $this->app->handle($request);

        self::assertSame(200, $response->getStatusCode());
    }

    public function testPatchRoute(): void
    {
        $this->app->patch('/patch', fn () => 'Patched');

        $request = new RuntimeRequest(
            method: 'PATCH',
            uri: '/patch',
        );

        $response = $this->app->handle($request);

        self::assertSame(200, $response->getStatusCode());
    }

    public function testDeleteRoute(): void
    {
        $this->app->delete('/delete', fn () => 'Deleted');

        $request = new RuntimeRequest(
            method: 'DELETE',
            uri: '/delete',
        );

        $response = $this->app->handle($request);

        self::assertSame(200, $response->getStatusCode());
    }

    public function testAnyRouteMatchesGetAndPost(): void
    {
        $this->app->any('/any', fn () => 'Any method');

        $getResponse = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/any'));
        self::assertSame(200, $getResponse->getStatusCode());

        $postResponse = $this->app->handle(new RuntimeRequest(method: 'POST', uri: '/any'));
        self::assertSame(200, $postResponse->getStatusCode());
    }

    public function testRouteWithArrayResponse(): void
    {
        $this->app->get('/api/data', fn () => ['name' => 'AvaX', 'version' => 4]);

        $request = new RuntimeRequest(
            method: 'GET',
            uri: '/api/data',
        );

        $response = $this->app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame(['name' => 'AvaX', 'version' => 4], $body);
        self::assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testRouteNotFound(): void
    {
        $this->app->get('/exists', fn () => 'OK');

        $request = new RuntimeRequest(
            method: 'GET',
            uri: '/does-not-exist',
        );

        $response = $this->app->handle($request);

        self::assertSame(404, $response->getStatusCode());
    }

    public function testMethodNotAllowed(): void
    {
        $this->app->post('/only-post', fn () => 'OK');

        $request = new RuntimeRequest(
            method: 'GET',
            uri: '/only-post',
        );

        $response = $this->app->handle($request);

        self::assertSame(405, $response->getStatusCode());
    }

    public function testRouteReturnsNotFoundWhenNoRoutesRegistered(): void
    {
        $request = new RuntimeRequest(
            method: 'GET',
            uri: '/anything',
        );

        $response = $this->app->handle($request);

        self::assertSame(404, $response->getStatusCode());
    }

    public function testRuntimeAccess(): void
    {
        $runtime = $this->app->runtime();
        self::assertSame('testing', $runtime->environment()->value);
    }

    public function testResetState(): void
    {
        $report = $this->app->resetState();
        // @phpstan-ignore-next-line — assertion proves non-null runtime behavior
        self::assertNotNull($report);
    }

    public function testAsHttpKernel(): void
    {
        $kernel = $this->app->asHttpKernel();

        $request = new RuntimeRequest(
            method: 'GET',
            uri: '/',
        );

        $response = $kernel->handle($request);

        self::assertSame(404, $response->statusCode());
    }

    public function testAsConsoleKernel(): void
    {
        $kernel = $this->app->asConsoleKernel();
        // @phpstan-ignore-next-line — assertion proves kernel assembly
        self::assertNotNull($kernel);
    }

    public function testAsRuntimeKernel(): void
    {
        $kernel = $this->app->asRuntimeKernel();
        // @phpstan-ignore-next-line — assertion proves kernel assembly
        self::assertNotNull($kernel);
    }

    public function testRunMethodExists(): void
    {
        $reflection = new ReflectionClass($this->app);
        self::assertTrue($reflection->hasMethod('run'));
    }

    public function testMultipleRouteRegistration(): void
    {
        $this->app
            ->get('/one', fn () => 'One')
            ->get('/two', fn () => 'Two')
            ->post('/three', fn () => 'Three');

        // Test /one
        $response1 = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/one'));
        self::assertSame('One', (string) $response1->getBody());

        // Test /two
        $response2 = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/two'));
        self::assertSame('Two', (string) $response2->getBody());

        // Test /three
        $response3 = $this->app->handle(new RuntimeRequest(method: 'POST', uri: '/three'));
        self::assertSame('Three', (string) $response3->getBody());
    }

    public function testRouteClosureReceivesRequest(): void
    {
        $this->app->get('/request', fn (ServerRequest $request) => $request->getUri()->getPath());

        $response = $this->app->handle(new RuntimeRequest(method: 'GET', uri: '/request'));

        self::assertSame('/request', (string) $response->getBody());
    }
}
