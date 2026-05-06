<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Framework;

use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class HandleIncomingHttpIntegrationTest extends TestCase
{
    public function test_route_backed_http_handler_reuses_request_and_router_components(): void
    {
        $application = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpRouteDefinitions(
                    routeDefinitions: static function (RouterInterface $router): void {
                        $router->post(
                            path   : '/users/42',
                            action : static function (ServerRequestInterface $request): string {
                                $parsedBody = $request->getParsedBody();
                                $name       = is_array($parsedBody) ? ($parsedBody['name'] ?? '') : '';

                                return '42:' . $name;
                            },
                        )->name(name: 'users.show');
                    },
                ),
        );

        $response = $application->http()->handle(
            request: new RuntimeRequest(
                method     : 'POST',
                uri        : '/users/42',
                headers    : ['Content-Type' => ['application/json']],
                body       : '{"name":"Ana"}',
                attributes : ['request-id' => 'abc-123'],
            ),
        );

        self::assertSame(200, $response->statusCode());
        self::assertSame('42:Ana', $response->body());
        self::assertFalse($application->requestScopes()->hasCurrent());
    }

    public function test_existing_route_file_runs_through_the_framework_http_flow(): void
    {
        $application = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpRoutes(routesFile: 'tests/fixtures/framework_http_routes.php'),
        );

        $healthResponse = $application->http()->handle(
            request: new RuntimeRequest(method: 'GET', uri: '/health'),
        );
        $missingResponse = $application->http()->handle(
            request: new RuntimeRequest(method: 'GET', uri: '/missing'),
        );

        self::assertSame(200, $healthResponse->statusCode());
        self::assertSame('ok', $healthResponse->body());
        self::assertSame(404, $missingResponse->statusCode());
        self::assertStringContainsString('Route not found', $missingResponse->body());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 3);
    }
}
