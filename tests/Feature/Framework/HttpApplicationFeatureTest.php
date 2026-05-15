<?php

declare(strict_types=1);

namespace Avax\Tests\Feature\Framework;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Configuration\BuildApplication\Builders\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;

final class HttpApplicationFeatureTest extends TestCase
{
    public function test_http_public_surface_runs_a_request_and_then_allows_state_reset(): void
    {
        $avax = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpHandler(
                    httpHandler: static fn (RuntimeRequest $runtimeRequest): RuntimeResponse => new RuntimeResponse(statusCode: 200, body: 'hello '.$runtimeRequest->uri()),
                ),
        );

        $runtimeResponse = $avax->http()->handle(
            request: new RuntimeRequest(method: 'GET', uri: '/world'),
        );

        self::assertSame('hello /world', $runtimeResponse->body());
        self::assertNotNull($avax->context()->lastResult());

        $avax->resetState();

        self::assertNull($avax->context()->lastResult());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 3);
    }
}
