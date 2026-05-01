<?php

declare(strict_types=1);

namespace Avax\Tests\Feature\Framework;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;

final class HttpApplicationFeatureTest extends TestCase
{
    public function test_http_public_surface_runs_a_request_and_then_allows_state_reset(): void
    {
        $application = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpHandler(
                    httpHandler: static fn (RuntimeRequest $request) : string => 'hello ' . $request->uri(),
                ),
        );

        $response = $application->http()->handle(
            request: new RuntimeRequest(method: 'GET', uri: '/world'),
        );

        self::assertSame('hello /world', $response->body());
        self::assertNotNull($application->context()->lastResult());

        $application->resetState();

        self::assertNull($application->context()->lastResult());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 3);
    }
}
