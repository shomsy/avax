<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Examples\GoldenPathRuntimeApp\WebhookIngestionApp;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves: HTTP kernel handles requests through middleware pipeline to router dispatch.
 */
final class HttpLifecycleTest extends TestCase
{
    #[Test]
    public function healthEndpointReturns200() : void
    {
        $avax = $this->bootAvax();

        $response = $avax->http()->handle(
            new RuntimeRequest(method: 'GET', uri: '/health'),
        );

        self::assertSame(200, $response->statusCode());
        self::assertStringContainsString('healthy', $response->body());
    }

    private function bootAvax() : Avax
    {
        $projectPath = new ProjectPath('/home/shomsy/projects/avax');
        $environment = EnvironmentName::Testing;

        return Avax::boot(WebhookIngestionApp::createBuilder($projectPath, $environment));
    }

    #[Test]
    public function webhookIngestEndpointReturns200() : void
    {
        $avax = $this->bootAvax();

        $response = $avax->http()->handle(
            new RuntimeRequest(
                method : 'POST',
                uri    : '/webhooks/ingest',
                headers: ['Content-Type' => ['application/json']],
                body   : '{"source":"github","event":"push"}',
            ),
        );

        self::assertSame(200, $response->statusCode());
        self::assertStringContainsString('accepted', $response->body());
        self::assertStringContainsString('webhook_id', $response->body());
    }

    #[Test]
    public function unknownRouteReturns404() : void
    {
        $avax = $this->bootAvax();

        $response = $avax->http()->handle(
            new RuntimeRequest(method: 'GET', uri: '/unknown'),
        );

        self::assertSame(404, $response->statusCode());
        self::assertStringContainsString('not found', $response->body());
    }

    #[Test]
    public function webhookStatusEndpointWithQueryParams() : void
    {
        $avax = $this->bootAvax();

        $response = $avax->http()->handle(
            new RuntimeRequest(method: 'GET', uri: '/webhooks/status?id=wh_abc123'),
        );

        self::assertSame(200, $response->statusCode());
        self::assertStringContainsString('wh_abc123', $response->body());
        self::assertStringContainsString('processed', $response->body());
    }

    #[Test]
    public function methodNotAllowedReturns405() : void
    {
        $avax = $this->bootAvax();

        // /health is registered as GET, trying POST should return 405
        $response = $avax->http()->handle(
            new RuntimeRequest(method: 'POST', uri: '/health'),
        );

        self::assertSame(405, $response->statusCode());
    }
}
