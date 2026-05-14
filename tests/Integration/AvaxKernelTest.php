<?php

declare(strict_types=1);

namespace Avax\Tests\Integration;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Avax\Components\HTTP\Request\System\Configuration\RequestBuilder;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Configuration\BuildApplication\ApplicationBuilder;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\RunDoctor\RunDoctor;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Framework\System\PublicSurface\Avax;
use Avax\Tests\TestCase;

final class AvaxKernelTest extends TestCase
{
    public function test_golden_path_boot_and_handle_http_request() : void
    {
        $responses = new Responses();

        // 1. Setup Application Builder with routes
        $builder = (new ApplicationBuilder(
            new ProjectPath(__DIR__ . '/../../'),
            EnvironmentName::Testing,
            new SystemClock(),
            new RunDoctor(),
            new HandleIncomingHttp(responseFactory: new ResponseFactory()),
            new Filesystem(),
            new ResponseFactory(),
            'avax',
        ))->withHttpRouteDefinitions(function (RouterInterface $router) use ($responses) {
            $router->get('/', fn () => $responses->send('Hello from Avax Kernel!'));
        });

        // 2. Boot Avax
        $avax = Avax::boot($builder);

        // 3. Create Request
        $request = new RuntimeRequest(
            method: 'GET',
            uri   : '/',
        );

        // 4. Handle Request through Kernel
        $response = $avax->http()->handle($request);

        // 5. Assertions
        $this->assertInstanceOf(RuntimeResponse::class, $response);
        $this->assertSame(200, $response->statusCode());
        $this->assertSame('Hello from Avax Kernel!', $response->body());
    }

    public function test_kernel_state_reset_works() : void
    {
        $builder = new ApplicationBuilder(
            new ProjectPath(__DIR__ . '/../../'),
            EnvironmentName::Testing,
            new SystemClock(),
            new RunDoctor(),
            new HandleIncomingHttp(responseFactory: new ResponseFactory()),
            new Filesystem(),
            new ResponseFactory(),
            'avax',
        );
        $avax    = Avax::boot($builder);

        $report = $avax->resetState();

        $this->assertNotEmpty($report->resetComponents());
        // We expect at least the basic registries to be reset
    }
}
