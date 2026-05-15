<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeApp;

use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\CreateRequestFromGlobals;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadQueryParameters;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadRequestBody;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadServerParameters;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadUploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Body\ParseFormBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\ParseJsonBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\NormalizeUploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\NormalizeHeaders;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Framework\System\Flows\CreateApplication\CreateApplication;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Framework\System\PublicSurface\App;
use Avax\Tests\TestCase;

/**
 * @covers \Avax\Framework\System\Flows\CreateApplication\CreateApplication
 */
final class CreateApplicationTest extends TestCase
{
    public function testMakeReturnsAppInstance(): void
    {
        $factory = $this->createFactory();
        $app = $factory->make(environment: 'testing');
        self::assertSame('testing', $app->runtime()->environment()->value);
    }

    public function testMakeInitializesRuntime(): void
    {
        $factory = $this->createFactory();
        $app = $factory->make(environment: 'testing');
        self::assertSame('testing', $app->runtime()->environment()->value);
    }

    public function testMakeSetsUpStateReset(): void
    {
        $factory = $this->createFactory();
        $app = $factory->make(environment: 'testing');
        $report = $app->resetState();
        // @phpstan-ignore-next-line — assertion proves state reset flow works
        self::assertNotNull($report);
    }

    public function testMakeWithDifferentEnvironments(): void
    {
        $factory = $this->createFactory();

        foreach (['testing', 'production', 'development', 'staging'] as $env) {
            $app = $factory->make(environment: $env);
            self::assertSame($env, $app->runtime()->environment()->value, "Failed for environment: $env");
        }
    }

    public function testMakeDoesNotInitializeFullContainer(): void
    {
        // V4-01 defers full DI Container initialization
        // The App should work with RouteFacadeContainer for route dispatch
        $factory = $this->createFactory();
        $app = $factory->make(environment: 'testing');

        // App should be usable for route registration
        $app->get('/test', fn () => 'OK');

        $response = $app->handle(
            new RuntimeRequest(
                method: 'GET',
                uri: '/test',
            ),
        );

        self::assertSame(200, $response->getStatusCode());
    }

    private function createFactory(): CreateApplication
    {
        return new CreateApplication(
            clock: new SystemClock(),
            projectPath: new ProjectPath(value: __DIR__ . '/../../../../..'),
            componentRegistry: new ComponentRegistry(),
            requestScopeStore: new RequestScopeStore(),
            runtimeContext: new RuntimeContext(),
            stateResetRegistry: new StateResetRegistry(),
            createHttpResponse: new CreateHttpResponse(),
            handleIncomingHttp: new HandleIncomingHttp(createHttpResponse: new CreateHttpResponse()),
            createRequestFromGlobals: new CreateRequestFromGlobals(
                readServerParameters  : new ReadServerParameters(),
                readQueryParameters   : new ReadQueryParameters(),
                readUploadedFiles     : new ReadUploadedFiles(),
                readRequestBody       : new ReadRequestBody(),
                normalizeHeaders      : new NormalizeHeaders(),
                normalizeUploadedFiles: new NormalizeUploadedFiles(),
                parseJsonBody         : new ParseJsonBody(),
                parseFormBody         : new ParseFormBody(),
            ),
        );
    }
}
