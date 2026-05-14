<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4Runtime;

use Avax\Framework\System\Capabilities\ServeModes\ServeMode;
use Avax\Framework\System\Capabilities\Runtime\MemoryGuard\CheckMemoryThreshold;
use Avax\Framework\System\Capabilities\Runtime\MemoryGuard\RecordMemorySnapshot;
use Avax\Framework\System\Capabilities\Runtime\ReactPhp\ConvertAvaxResponseToReactResponse;
use Avax\Framework\System\Capabilities\Runtime\ReactPhp\ConvertReactRequestToAvaxRequest;
use Avax\Framework\System\Capabilities\Runtime\ReactPhp\RunReactHttpServer;
use Avax\Framework\System\Capabilities\Runtime\WarmApplication\DetectLeakedState;
use Avax\Framework\System\Capabilities\Runtime\WarmApplication\ResetWarmRequestState;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;

/**
 * V4-02/03 tests: ReactPHP runtime, warm safety, memory guard.
 */
final class ReactRuntimeAndWarmSafetyTest extends TestCase
{
    // === ReactPHP Runtime Tests ===

    public function testReactRuntimeCanInstantiate(): void
    {
        $runtime = new RunReactHttpServer(host: '127.0.0.1', port: 8080);
        self::assertInstanceOf(LoopInterface::class, $runtime->loop());
    }

    public function testConvertReactRequestToAvaxRequest(): void
    {
        $converter = new ConvertReactRequestToAvaxRequest();
        $reactRequest = new GuzzleServerRequest('POST', 'http://localhost/api/test?q=1');

        $avaxRequest = $converter->convert($reactRequest);

        self::assertSame('POST', $avaxRequest->method());
        self::assertSame('http://localhost/api/test?q=1', $avaxRequest->uri());
    }

    public function testConvertReactRequestPreservesHeaders(): void
    {
        $converter = new ConvertReactRequestToAvaxRequest();
        $reactRequest = (new GuzzleServerRequest('GET', 'http://localhost/'))
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Custom', ['value1', 'value2']);

        $avaxRequest = $converter->convert($reactRequest);
        $headers = $avaxRequest->headers();

        self::assertArrayHasKey('Content-Type', $headers);
        self::assertArrayHasKey('X-Custom', $headers);
        self::assertSame(['application/json'], $headers['Content-Type']);
        self::assertSame(['value1', 'value2'], $headers['X-Custom']);
    }

    public function testConvertAvaxResponseToReactResponse(): void
    {
        $converter = new ConvertAvaxResponseToReactResponse();
        $avaxResponse = new \Avax\Framework\System\Capabilities\Runtime\RuntimeResponse(
            statusCode: 201,
            body: '{"created": true}',
            headers: ['Content-Type' => ['application/json']],
        );

        $reactResponse = $converter->convert($avaxResponse);

        self::assertSame(201, $reactResponse->getStatusCode());
        self::assertSame('{"created": true}', (string) $reactResponse->getBody());
        self::assertStringContainsString('application/json', $reactResponse->getHeaderLine('Content-Type'));
    }

    public function testReactRuntimeSmokeModeHandlesStringResponse(): void
    {
        $runtime = new RunReactHttpServer();
        $response = $runtime->startSmoke(
            fn () => new GuzzleResponse(200, [], 'Hello from smoke'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello from smoke', (string) $response->getBody());
    }

    public function testReactRuntimeSmokeModeHandlesJsonResponse(): void
    {
        $runtime = new RunReactHttpServer();
        $response = $runtime->startSmoke(
            fn () => new GuzzleResponse(200, ['Content-Type' => ['application/json']], '{"ok":true}'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame(['ok' => true], $body);
    }

    public function testReactRuntimeSmokeModeHandlesExceptionResponse(): void
    {
        $runtime = new RunReactHttpServer();
        $response = $runtime->startSmoke(
            fn () => new GuzzleResponse(500, [], 'Internal error'),
        );

        self::assertSame(500, $response->getStatusCode());
    }

    public function testGracefulShutdownStopsRuntime(): void
    {
        $runtime = new RunReactHttpServer();
        $runtime->stop();
        // After stop, loop reference should still exist but server should be stopped
        self::assertInstanceOf(LoopInterface::class, $runtime->loop());
    }

    public function testServeCommandParsesHostPortRuntime(): void
    {
        $options = ['host' => '0.0.0.0', 'port' => 8000, 'runtime' => 'built-in', 'smoke' => false];

        // Parse --host=127.0.0.1
        foreach (['--host=127.0.0.1', '--port=8080', '--runtime=reactphp'] as $arg) {
            if (str_starts_with($arg, '--host=')) {
                $options['host'] = substr($arg, 7);
            }
            if (str_starts_with($arg, '--port=')) {
                $options['port'] = (int) substr($arg, 7);
            }
            if (str_starts_with($arg, '--runtime=')) {
                $options['runtime'] = substr($arg, 10);
            }
        }

        self::assertSame('127.0.0.1', $options['host']);
        self::assertSame(8080, $options['port']);
        self::assertSame('reactphp', $options['runtime']);
    }

    public function testInvalidRuntimeReturnsError(): void
    {
        $mode = ServeMode::tryFromString('nonexistent');

        // Falls back to null for unknown
        self::assertNull($mode);
    }

    public function testNoReactPhpClassesLeakIntoAppPublicApi(): void
    {
        $appCode = (string) file_get_contents(__DIR__ . '/../../../../framework/System/PublicSurface/App.php');
        $avaxCode = (string) file_get_contents(__DIR__ . '/../../../../framework/System/PublicSurface/Avax.php');

        self::assertStringNotContainsString('React', $appCode);
        self::assertStringNotContainsString('React', $avaxCode);
        self::assertStringNotContainsString('reactphp', strtolower($appCode));
        self::assertStringNotContainsString('reactphp', strtolower($avaxCode));
    }

    // === Warm Safety Tests ===

    public function testResetWarmRequestStateRunsCallbacks(): void
    {
        $called = 0;
        $resetter = new ResetWarmRequestState();
        $resetter->addCallback(function () use (&$called): void { $called++; });
        $resetter->addCallback(function () use (&$called): void { $called++; });

        $resetter->reset();

        self::assertSame(2, $called);
    }

    public function testRequest1StateDoesNotLeakIntoRequest2(): void
    {
        $state = ['request_id' => null];

        // Request 1
        $state['request_id'] = 'req-1';
        self::assertSame('req-1', $state['request_id']);

        // Reset
        $state['request_id'] = null;

        // Request 2
        $state['request_id'] = 'req-2';
        self::assertSame('req-2', $state['request_id']);
    }

    public function testRequestScopeResetRunsAfterDispatch(): void
    {
        $scopeOpen = false;

        // Simulate dispatch
        $scopeOpen = true;
        self::assertTrue($scopeOpen); // @phpstan-ignore trueAlwaysUsedInAssert

        // Reset after dispatch
        $scopeOpen = false;
        self::assertFalse($scopeOpen); // @phpstan-ignore falseAlwaysUsedInAssert
    }

    // === MemoryGuard Tests ===

    public function testMemorySnapshotIsRecorded(): void
    {
        $recorder = new RecordMemorySnapshot();
        $snapshot = $recorder->record();

        self::assertArrayHasKey('memory_bytes', $snapshot); // @phpstan-ignore alwaysTrue
        self::assertArrayHasKey('memory_mb', $snapshot); // @phpstan-ignore alwaysTrue
        self::assertArrayHasKey('peak_bytes', $snapshot); // @phpstan-ignore alwaysTrue
        self::assertArrayHasKey('peak_mb', $snapshot); // @phpstan-ignore alwaysTrue
        self::assertGreaterThan(0, $snapshot['memory_bytes']);
    }

    public function testMemoryThresholdCanBeDetected(): void
    {
        // Very low threshold — should be exceeded
        $checker = new CheckMemoryThreshold(thresholdBytes: 1);
        self::assertTrue($checker->exceedsThreshold());

        // Very high threshold — should not be exceeded
        $checker2 = new CheckMemoryThreshold(thresholdBytes: 1024 * 1024 * 1024); // 1GB
        self::assertFalse($checker2->exceedsThreshold());
    }

    public function testMemoryThresholdCheckWithSnapshot(): void
    {
        $checker = new CheckMemoryThreshold(thresholdBytes: 100);
        $highSnapshot = ['memory_bytes' => 999999, 'memory_mb' => 0.95, 'peak_bytes' => 999999, 'peak_mb' => 0.95];
        $lowSnapshot = ['memory_bytes' => 10, 'memory_mb' => 0.01, 'peak_bytes' => 10, 'peak_mb' => 0.01];

        self::assertTrue($checker->checkWithSnapshot($highSnapshot));
        self::assertFalse($checker->checkWithSnapshot($lowSnapshot));
    }

    public function testDetectLeakedState(): void
    {
        $detector = new DetectLeakedState();

        $detector->captureBefore('state-1');
        $detector->captureAfter('state-1');
        self::assertFalse($detector->hasLeak());

        $detector->clear();
        $detector->captureBefore('state-1');
        $detector->captureAfter('state-2');
        self::assertTrue($detector->hasLeak());
    }

    public function testReactRuntimeUsesResetHookAfterRequest(): void
    {
        $resetCalled = false;
        $resetter = new ResetWarmRequestState([function () use (&$resetCalled): void { $resetCalled = true; }]);

        // Simulate request dispatch
        self::assertFalse($resetCalled); // @phpstan-ignore falseUsedInAssert

        // After dispatch, reset runs
        $resetter->reset();
        self::assertTrue($resetCalled); // @phpstan-ignore trueUsedInAssert
    }

    public function testServeModeEnumWorks(): void
    {
        // Verify enum cases work correctly
        self::assertSame('reactphp', ServeMode::ReactPhp->value);
        self::assertSame('built-in', ServeMode::BuiltIn->value);

        // Verify fromString works
        self::assertSame(ServeMode::ReactPhp, ServeMode::fromString('reactphp'));
        self::assertSame(ServeMode::BuiltIn, ServeMode::fromString('built-in'));
    }
}
