<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Examples\GoldenPathRuntimeApp\WebhookIngestionApp;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRequest;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\PublicSurface\Avax;
use Avax\Tests\GoldenPathRuntime\Support\TestWorkerRuntime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves: WorkerLoop processes requests, resets state between them,
 * and shuts down cleanly.
 */
final class WorkerLoopTest extends TestCase
{
    #[Test]
    public function workerLoopProcessesRequestsAndResets() : void
    {
        $avax = $this->bootAvax();

        $workerRuntime = new TestWorkerRuntime([
                                                   new WorkerRequest('req-1', new RuntimeRequest('GET', '/health')),
                                                   new WorkerRequest('req-2', new RuntimeRequest('POST', '/webhooks/ingest', ['Content-Type' => ['application/json']], '{"source":"test"}')),
                                               ]);

        $lifecycle = $avax->runtime()->runWorker($workerRuntime);

        self::assertSame('test-worker', $lifecycle->runtimeName());
        self::assertNotNull($lifecycle->stoppedAt());
        self::assertCount(2, $lifecycle->handledRequestIds());

        // WorkerLoop resets state in its finally block
        self::assertFalse($avax->requestScopes()->hasCurrent());
    }

    private function bootAvax() : Avax
    {
        $projectPath = new ProjectPath('/home/shomsy/projects/avax');
        $environment = EnvironmentName::Testing;

        return Avax::boot(WebhookIngestionApp::createBuilder($projectPath, $environment));
    }

    #[Test]
    public function workerLoopCollectsResponses() : void
    {
        $avax = $this->bootAvax();

        $workerRuntime = new TestWorkerRuntime([
                                                   new WorkerRequest('req-1', new RuntimeRequest('GET', '/health')),
                                               ]);

        $avax->runtime()->runWorker($workerRuntime);

        $responses = $workerRuntime->responses();

        self::assertCount(1, $responses);
        self::assertSame('req-1', $responses[0]->requestId());
        self::assertSame(200, $responses[0]->response()->statusCode());
    }

    #[Test]
    public function workerLoopHandlesEmptyQueue() : void
    {
        $avax = $this->bootAvax();

        $workerRuntime = new TestWorkerRuntime([]);

        $lifecycle = $avax->runtime()->runWorker($workerRuntime);

        self::assertNotNull($lifecycle->stoppedAt());
        self::assertCount(0, $lifecycle->handledRequestIds());
    }

    #[Test]
    public function workerLoopHandles404Route() : void
    {
        $avax = $this->bootAvax();

        $workerRuntime = new TestWorkerRuntime([
                                                   new WorkerRequest('req-1', new RuntimeRequest('GET', '/nonexistent')),
                                               ]);

        $lifecycle = $avax->runtime()->runWorker($workerRuntime);

        $responses = $workerRuntime->responses();

        self::assertCount(1, $responses);
        self::assertSame(404, $responses[0]->response()->statusCode());
    }

    #[Test]
    public function workerLifecycleRecordsHandledRequests() : void
    {
        $avax = $this->bootAvax();

        $workerRuntime = new TestWorkerRuntime([
                                                   new WorkerRequest('a', new RuntimeRequest('GET', '/health')),
                                                   new WorkerRequest('b', new RuntimeRequest('GET', '/health')),
                                                   new WorkerRequest('c', new RuntimeRequest('GET', '/health')),
                                               ]);

        $lifecycle = $avax->runtime()->runWorker($workerRuntime);

        self::assertCount(3, $lifecycle->handledRequestIds());
        self::assertContains('a', $lifecycle->handledRequestIds());
        self::assertContains('b', $lifecycle->handledRequestIds());
        self::assertContains('c', $lifecycle->handledRequestIds());
    }
}
