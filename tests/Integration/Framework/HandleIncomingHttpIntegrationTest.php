<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Framework;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use Avax\HTTP\Router\RouterInterface;
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
                            path   : '/users/{id}',
                            action : static function (ServerRequestInterface $request): string {
                                $parsedBody = $request->getParsedBody();
                                $name       = is_array($parsedBody) ? ($parsedBody['name'] ?? '') : '';

                                return (string) $request->getAttribute(name: 'id') . ':' . $name;
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
                ->withHttpRoutes(routesFile: 'Presentation/HTTP/routes/web.routes.php'),
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
        self::assertStringContainsString('Route not found for [GET] /missing', $missingResponse->body());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 3);
    }
}
