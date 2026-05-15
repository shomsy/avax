<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Framework\System\Configuration\BuildApplication\Builders\ApplicationBuilder;

use Avax\Examples\GoldenPathRuntimeApp\WebhookIngestionApp;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves: StateResetRegistry resets cleanly between requests,
 * request scopes are properly opened and closed.
 */
final class RuntimeResetProofTest extends TestCase
{
    #[Test]
    public function runtimeStateResetsBetweenRequests() : void
    {
        $avax = $this->bootAvax();

        // First request
        $response1 = $avax->http()->handle(
            new RuntimeRequest(method: 'GET', uri: '/health'),
        );
        self::assertSame(200, $response1->statusCode());

        // Reset
        $resetReport = $avax->resetState();
        self::assertEmpty($resetReport->failures());

        // Second request — should work with clean state
        $response2 = $avax->http()->handle(
            new RuntimeRequest(method: 'GET', uri: '/health'),
        );
        self::assertSame(200, $response2->statusCode());
    }

    private function bootAvax() : Avax
    {
        $projectPath = new ProjectPath('/home/shomsy/projects/avax');
        $environment = EnvironmentName::Testing;

        return Avax::boot(WebhookIngestionApp::createBuilder($projectPath, $environment));
    }

    #[Test]
    public function requestScopeClosesAfterHandle() : void
    {
        $avax = $this->bootAvax();

        // No scope should be open before request
        self::assertFalse($avax->requestScopes()->hasCurrent());

        $avax->http()->handle(
            new RuntimeRequest(method: 'GET', uri: '/health'),
        );

        // Scope should be closed after handle (HandleIncomingHttp closes in finally)
        self::assertFalse($avax->requestScopes()->hasCurrent());
    }

    #[Test]
    public function runtimeStateTracksBootCount() : void
    {
        $avax = $this->bootAvax();

        self::assertTrue($avax->state()->isBooted());
        self::assertGreaterThan(0, $avax->state()->bootCount());
    }

    #[Test]
    public function runtimeContextTracksRequests() : void
    {
        $avax = $this->bootAvax();

        $avax->http()->handle(
            new RuntimeRequest(method: 'GET', uri: '/health'),
        );

        // Context should have recorded a result
        $result = $avax->context()->lastResult();
        self::assertNotNull($result);
    }

    #[Test]
    public function multipleRequestsResetCleanly() : void
    {
        $avax = $this->bootAvax();

        for ($i = 0; $i < 5; $i++) {
            $response = $avax->http()->handle(
                new RuntimeRequest(method: 'GET', uri: '/health'),
            );
            self::assertSame(200, $response->statusCode());

            $resetReport = $avax->resetState();
            self::assertEmpty($resetReport->failures());
            self::assertFalse($avax->requestScopes()->hasCurrent());
        }
    }
}
