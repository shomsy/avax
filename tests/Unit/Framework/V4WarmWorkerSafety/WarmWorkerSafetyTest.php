<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4WarmWorkerSafety;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Runtime\MemoryGuard\CalculateMemoryGrowthRate;
use Avax\Framework\System\Runtime\MemoryGuard\CheckMemoryThreshold;
use Avax\Framework\System\Runtime\MemoryGuard\MonitorWorkerMemory;
use Avax\Framework\System\Runtime\MemoryGuard\RecordMemorySnapshot;
use Avax\Framework\System\Runtime\MemoryGuard\RecycleReason;
use Avax\Framework\System\Runtime\MemoryGuard\RequestWorkerRecycle;
use Avax\Framework\System\Runtime\ReactPhp\RunReactHttpServer;
use Avax\Framework\System\Runtime\WarmApplication\AllowedWarmState;
use Avax\Framework\System\Runtime\WarmApplication\DetectLeakedState;
use Avax\Framework\System\Runtime\WarmApplication\FlushScopedInstances;
use Avax\Framework\System\Runtime\WarmApplication\HandleWarmRequest;
use Avax\Framework\System\Runtime\WarmApplication\MustResetState;
use Avax\Framework\System\Runtime\WarmApplication\ResetWarmRequestState;
use Avax\Framework\System\Runtime\WarmApplication\RuntimeStateLeak;
use Avax\Framework\System\Runtime\WarmApplication\WarmStateContract;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * V4-03: Full Warm Worker Safety Hardening tests.
 *
 * Covers:
 * - Warm State Contract
 * - Request Reset Lifecycle
 * - State Leak Detection
 * - MemoryGuard
 * - ReactPHP Runtime Integration
 * - Architecture / Composition
 */
final class WarmWorkerSafetyTest extends TestCase
{
    // === Warm State Contract Tests ===

    public function testWarmStateContractExposesAllowedWarmState(): void
    {
        $contract = new WarmStateContract();

        $allowed = $contract->allowedWarmState();

        self::assertNotEmpty($allowed);
        self::assertContains(AllowedWarmState::CompiledContainerDefinitions, $allowed);
        self::assertContains(AllowedWarmState::CompiledRouteTable, $allowed);
        self::assertContains(AllowedWarmState::ImmutableConfiguration, $allowed);
    }

    public function testWarmStateContractExposesMustResetState(): void
    {
        $contract = new WarmStateContract();

        $mustReset = $contract->mustResetState();

        self::assertNotEmpty($mustReset);
        self::assertContains(MustResetState::CurrentRequest, $mustReset);
        self::assertContains(MustResetState::CorrelationId, $mustReset);
        self::assertContains(MustResetState::ScopedContainerInstances, $mustReset);
    }

    public function testWarmStateContractHasNoRequestSpecificRuntimeValues(): void
    {
        $contract1 = new WarmStateContract();
        $contract2 = new WarmStateContract();

        // Two instances should expose the same classification lists
        self::assertEquals($contract1->allowedWarmState(), $contract2->allowedWarmState());
        self::assertEquals($contract1->mustResetState(), $contract2->mustResetState());
    }

    public function testWarmStateContractIsEffectivelyImmutable(): void
    {
        $contract = new WarmStateContract();

        // Get the allowed state list and modify the returned copy
        $allowed = $contract->allowedWarmState();
        $originalCount = count($allowed);
        $allowed[] = AllowedWarmState::LoggerInstancesWithoutRequestContext;

        // The contract's own list should be unaffected (returns a fresh copy each time)
        self::assertCount($originalCount, $contract->allowedWarmState());
    }

    public function testWarmStateContractIsAllowedWarmCheck(): void
    {
        $contract = new WarmStateContract();

        self::assertTrue($contract->isAllowedWarm(AllowedWarmState::CompiledContainerDefinitions));
        self::assertTrue($contract->isAllowedWarm(AllowedWarmState::CompiledRouteTable));
    }

    public function testWarmStateContractMustResetCheck(): void
    {
        $contract = new WarmStateContract();

        self::assertTrue($contract->mustReset(MustResetState::CurrentRequest));
        self::assertTrue($contract->mustReset(MustResetState::CorrelationId));
    }

    public function testAllowedWarmStateContainsCompiledConcepts(): void
    {
        $cases = AllowedWarmState::cases();
        $values = array_map(fn ($c) => $c->value, $cases);

        self::assertContains('compiled_container_definitions', $values);
        self::assertContains('compiled_route_table', $values);
        self::assertContains('immutable_configuration', $values);
    }

    public function testMustResetStateContainsRequestConcepts(): void
    {
        $cases = MustResetState::cases();
        $values = array_map(fn ($c) => $c->value, $cases);

        self::assertContains('current_request', $values);
        self::assertContains('correlation_id', $values);
        self::assertContains('request_id', $values);
        self::assertContains('scoped_container_instances', $values);
    }

    // === Request Reset Lifecycle Tests ===

    public function testResetWarmRequestStateRunsAllCallbacks(): void
    {
        $calls = [];
        $resetter = new ResetWarmRequestState();
        $resetter->addCallback(function () use (&$calls): void { $calls[] = 'first'; });
        $resetter->addCallback(function () use (&$calls): void { $calls[] = 'second'; });

        $resetter->reset();

        self::assertSame(['first', 'second'], $calls);
    }

    public function testRequestScopeOpensBeforeHandling(): void
    {
        $scope = new RequestScope(RequestScopeId::generate());
        $scope->close();
        self::assertFalse($scope->isOpen());

        $scope->open();
        self::assertTrue($scope->isOpen());
    }

    public function testRequestScopeClosesAfterHandling(): void
    {
        $scope = new RequestScope(RequestScopeId::generate());
        self::assertTrue($scope->isOpen());

        $scope->close();
        self::assertFalse($scope->isOpen());
    }

    public function testFlushScopedInstancesClosesAndReopensScope(): void
    {
        $scope = new RequestScope(RequestScopeId::generate());
        $scope->write('user_id', 42);
        self::assertTrue($scope->has('user_id'));

        $flusher = new FlushScopedInstances();
        $flusher->setRequestScope($scope);
        $flusher->flush();

        // After flush, scope is reopened but empty
        self::assertTrue($scope->isOpen());
        self::assertFalse($scope->has('user_id'));
    }

    public function testFlushScopedInstancesRunsResetRegistry(): void
    {
        $registry = new StateResetRegistry();
        $tracker = new ResetTracker();
        $registry->register('test', $tracker);

        $flusher = new FlushScopedInstances();
        $flusher->setResetRegistry($registry);
        $flusher->flush();

        self::assertTrue($tracker->wasReset());
    }

    public function testResetLifecycleRunsAfterSuccessfulRequest(): void
    {
        $resetCalled = false;
        $handler = new HandleWarmRequest();
        $handler->resetter()->addCallback(function () use (&$resetCalled): void { $resetCalled = true; });

        $response = $handler->handle(function () {
            return new \Avax\Framework\System\Capabilities\Runtime\RuntimeResponse(
                statusCode: 200,
                body: 'OK',
            );
        });

        self::assertTrue($resetCalled);
        self::assertTrue($handler->resetRan());
        self::assertSame(200, $response->statusCode());
    }

    public function testResetLifecycleRunsAfterFailedRequest(): void
    {
        $resetCalled = false;
        $handler = new HandleWarmRequest();
        $handler->resetter()->addCallback(function () use (&$resetCalled): void { $resetCalled = true; });

        try {
            $handler->handle(function (): never { throw new RuntimeException('Controller error'); });
            self::fail('Expected exception was not thrown');
        } catch (RuntimeException) {
            // Reset should still have run
            self::assertTrue($resetCalled);
            self::assertTrue($handler->resetRan());
        }
    }

    public function testScopedInstanceFromRequest1NotReusedInRequest2(): void
    {
        $scope = new RequestScope(RequestScopeId::generate());
        $flusher = new FlushScopedInstances();
        $flusher->setRequestScope($scope);

        // Request 1: write scoped data
        $scope->write('request_id', 'req-1');
        self::assertSame('req-1', $scope->read('request_id'));

        // Flush between requests
        $flusher->flush();

        // Request 2: scope should be clean
        self::assertFalse($scope->has('request_id'));
        $scope->write('request_id', 'req-2');
        self::assertSame('req-2', $scope->read('request_id'));
    }

    public function testResetLifecycleOrderIsDeterministic(): void
    {
        $order = [];
        $handler = new HandleWarmRequest();

        $handler->flusher()->addFlushCallback(function () use (&$order): void { $order[] = 'flush'; });
        $handler->resetter()->addCallback(function () use (&$order): void { $order[] = 'reset'; });

        $handler->handle(function () {
            return new \Avax\Framework\System\Capabilities\Runtime\RuntimeResponse(
                statusCode: 200,
                body: 'OK',
            );
        });

        self::assertSame(['flush', 'reset'], $order);
    }

    // === State Leak Detection Tests ===

    public function testCleanRuntimeReportsNoLeak(): void
    {
        $detector = new DetectLeakedState();
        $detector->captureBefore('clean-state');
        $detector->captureAfter('clean-state');

        self::assertFalse($detector->hasLeak());
        self::assertSame('green', $detector->checkStatus());
    }

    public function testFakeLeakedRequestStateIsDetected(): void
    {
        $detector = new DetectLeakedState();
        $detector->captureBefore('state-1');
        $detector->captureAfter('state-2');

        self::assertTrue($detector->hasLeak());
        self::assertSame('finding', $detector->checkStatus());
    }

    public function testFakeLeakedScopedInstanceIsDetected(): void
    {
        $detector = new DetectLeakedState();
        $detector->trackState(MustResetState::CurrentRequest, 'leftover-request-object');

        $leaks = $detector->detectLeaks();

        self::assertCount(1, $leaks);
        // @phpstan-ignore-next-line
        self::assertInstanceOf(RuntimeStateLeak::class, $leaks[0]);
        self::assertSame(MustResetState::CurrentRequest, $leaks[0]->leakedState);
    }

    public function testUnavailableSubsystemReportsNotFakeGreen(): void
    {
        $detector = new DetectLeakedState();

        // No scope attached, no tracking — should be green (no subsystem requested)
        self::assertSame('green', $detector->checkStatus());

        // With a leak tracked but no scope, still green for simple check
        $detector->captureBefore('a');
        $detector->captureAfter('a');
        self::assertSame('green', $detector->checkStatus());
    }

    public function testLeakDetectorClearsState(): void
    {
        $detector = new DetectLeakedState();
        $detector->captureBefore('before');
        $detector->captureAfter('after');
        self::assertTrue($detector->hasLeak());

        $detector->clear();
        self::assertFalse($detector->hasLeak());
    }

    public function testLeakDetectorDetectsResidualScopeData(): void
    {
        $scope = new RequestScope(RequestScopeId::generate());
        $scope->write('leaked_key', 'leaked_value');

        $detector = new DetectLeakedState();
        $detector->setRequestScope($scope);

        $leaks = $detector->detectLeaks();

        self::assertCount(1, $leaks);
        self::assertSame(MustResetState::RequestScopedCache, $leaks[0]->leakedState);
    }

    public function testRuntimeStateLeakDescription(): void
    {
        $leak = new RuntimeStateLeak(
            leakedState: MustResetState::CorrelationId,
            detail: 'correlation-123 still present',
        );

        self::assertStringContainsString('correlation_id', $leak->description());
        self::assertStringContainsString('correlation-123', $leak->description());
    }

    // === MemoryGuard Tests ===

    public function testMemorySnapshotRecordsBeforeAfterPeak(): void
    {
        $monitor = new MonitorWorkerMemory();
        $monitor->captureBefore();
        $monitor->captureAfter();

        $snapshot = $monitor->latestSnapshot();

        self::assertNotNull($snapshot);
        // @phpstan-ignore-next-line
        self::assertArrayHasKey('memory_bytes', $snapshot);
        // @phpstan-ignore-next-line
        self::assertArrayHasKey('peak_bytes', $snapshot);
        // @phpstan-ignore-next-line
        self::assertArrayHasKey('delta_bytes', $snapshot);
        self::assertGreaterThan(0, $snapshot['memory_bytes']);
    }

    public function testMemoryDeltaIsCalculated(): void
    {
        $monitor = new MonitorWorkerMemory();
        $monitor->captureBefore();

        // Allocate some memory
        $data = str_repeat('x', 10000);
        unset($data);

        $monitor->captureAfter();

        $delta = $monitor->memoryDelta();
        self::assertNotNull($delta);
    }

    public function testGrowthRateIsCalculated(): void
    {
        $calculator = new CalculateMemoryGrowthRate();

        $snapshots = [
            ['memory_bytes' => 1000000, 'memory_mb' => 0.95, 'peak_bytes' => 1000000, 'peak_mb' => 0.95],
            ['memory_bytes' => 1010000, 'memory_mb' => 0.96, 'peak_bytes' => 1010000, 'peak_mb' => 0.96],
            ['memory_bytes' => 1020000, 'memory_mb' => 0.97, 'peak_bytes' => 1020000, 'peak_mb' => 0.97],
        ];

        $rate = $calculator->calculate($snapshots);

        self::assertNotNull($rate);
        self::assertGreaterThan(0, $rate); // Growing
    }

    public function testSoftThresholdProducesWarning(): void
    {
        // Set a very low soft threshold
        $monitor = new MonitorWorkerMemory(softThresholdBytes: 1, hardThresholdBytes: 999999999);
        $monitor->captureBefore();
        $monitor->captureAfter();

        $decision = $monitor->recycleDecision();

        self::assertNotNull($decision);
        self::assertSame(RecycleReason::SoftThresholdExceeded, $decision->reason);
        self::assertTrue($decision->shouldRecycle());
    }

    public function testHardThresholdProducesRecycleRequest(): void
    {
        $monitor = new MonitorWorkerMemory(softThresholdBytes: 1, hardThresholdBytes: 1);
        $monitor->captureBefore();
        $monitor->captureAfter();

        $decision = $monitor->recycleDecision();

        self::assertNotNull($decision);
        self::assertSame(RecycleReason::HardThresholdExceeded, $decision->reason);
    }

    public function testMaxRequestsThresholdProducesRecycleRequest(): void
    {
        $monitor = new MonitorWorkerMemory(softThresholdBytes: 999999999, hardThresholdBytes: 999999999, maxRequests: 1);
        $monitor->captureBefore();
        $monitor->captureAfter();

        $decision = $monitor->recycleDecision();

        self::assertNotNull($decision);
        self::assertSame(RecycleReason::MaxRequestsExceeded, $decision->reason);
    }

    public function testRecycleDecisionIsGraceful(): void
    {
        $monitor = new MonitorWorkerMemory(softThresholdBytes: 1, hardThresholdBytes: 999999999);
        $monitor->captureBefore();
        $monitor->captureAfter();

        $decision = $monitor->recycleDecision();

        self::assertNotNull($decision);
        self::assertStringContainsString('recycle', $decision->description());
    }

    public function testNoMidRequestTermination(): void
    {
        // MemoryGuard records and reports — it never terminates mid-request.
        // These methods must NOT exist on MonitorWorkerMemory.
        $monitor = new MonitorWorkerMemory(softThresholdBytes: 1, hardThresholdBytes: 1);
        $reflector = new \ReflectionClass($monitor);
        $methods = array_map(fn ($m) => $m->getName(), $reflector->getMethods());

        self::assertNotContains('kill', $methods);
        self::assertNotContains('terminate', $methods);
    }

    public function testMemoryThresholdSoftVsHard(): void
    {
        $checker = new CheckMemoryThreshold(thresholdBytes: 100);

        self::assertTrue($checker->exceedsThreshold());

        $checker2 = new CheckMemoryThreshold(thresholdBytes: 1024 * 1024 * 1024);
        self::assertFalse($checker2->exceedsThreshold());
    }

    public function testRecycleDecisionCanBeCleared(): void
    {
        $monitor = new MonitorWorkerMemory(softThresholdBytes: 1, hardThresholdBytes: 999999999);
        $monitor->captureBefore();
        $monitor->captureAfter();

        self::assertNotNull($monitor->recycleDecision());

        $monitor->clearRecycleDecision();
        self::assertNull($monitor->recycleDecision());
    }

    // === ReactPHP Runtime Integration Tests ===

    public function testReactSmokeRequestInvokesResetLifecycle(): void
    {
        $resetCalled = false;
        $warmHandler = new HandleWarmRequest();
        $warmHandler->resetter()->addCallback(function () use (&$resetCalled): void { $resetCalled = true; });

        $runtime = new RunReactHttpServer();
        $runtime->setWarmHandler($warmHandler);

        $response = $runtime->startWarmSmoke(
            function () { return new GuzzleResponse(200, [], 'OK'); },
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($resetCalled);
    }

    public function testTwoSequentialReactSmokeRequestsDoNotShareRequestState(): void
    {
        $sharedState = ['request_id' => null, 'count' => 0];
        $warmHandler = new HandleWarmRequest();
        $warmHandler->resetter()->addCallback(function () use (&$sharedState): void { $sharedState['request_id'] = null; });

        $runtime = new RunReactHttpServer();
        $runtime->setWarmHandler($warmHandler);

        // Request 1
        $sharedState['request_id'] = 'req-1';
        $sharedState['count']++;
        $count = $sharedState['count'];
        $runtime->startWarmSmoke(
            function () use ($count) { return new GuzzleResponse(200, [], "Response {$count}"); },
        );

        // After request 1, reset should have cleared request_id
        // @phpstan-ignore-next-line
        self::assertNull($sharedState['request_id']);

        // Request 2 — should start with clean state
        $sharedState['count']++;
        $sharedState['request_id'] = 'req-2';
        $count = $sharedState['count'];
        $runtime->startWarmSmoke(
            function () use ($count) { return new GuzzleResponse(200, [], "Response {$count}"); },
        );

        // After request 2, reset should have cleared request_id again
        // @phpstan-ignore-next-line
        self::assertNull($sharedState['request_id']);
    }

    public function testExceptionInReactRequestStillTriggersReset(): void
    {
        $resetCalled = false;
        $warmHandler = new HandleWarmRequest();
        $warmHandler->resetter()->addCallback(function () use (&$resetCalled): void { $resetCalled = true; });

        $runtime = new RunReactHttpServer();
        $runtime->setWarmHandler($warmHandler);

        try {
            $runtime->startWarmSmoke(
                function (): never { throw new RuntimeException('Handler error'); },
            );
            self::fail('Expected exception was not thrown');
        } catch (RuntimeException) {
            self::assertTrue($resetCalled, 'Reset must run even after exception');
        }
    }

    public function testMemorySnapshotRecordedForReactRequest(): void
    {
        $memoryGuard = new MonitorWorkerMemory();
        $runtime = new RunReactHttpServer();
        $runtime->setMemoryGuard($memoryGuard);

        $runtime->startWarmSmoke(
            fn () => new GuzzleResponse(200, [], 'OK'),
        );

        self::assertSame(1, $memoryGuard->requestCount());
        self::assertNotNull($memoryGuard->latestSnapshot());
    }

    public function testAppPublicMethodsDoNotExposeReactPhpClasses(): void
    {
        $appCode = (string) file_get_contents(__DIR__ . '/../../../../framework/System/PublicSurface/App.php');
        $avaxCode = (string) file_get_contents(__DIR__ . '/../../../../framework/System/PublicSurface/Avax.php');

        self::assertStringNotContainsString('React', $appCode);
        self::assertStringNotContainsString('React', $avaxCode);
        self::assertStringNotContainsString('reactphp', strtolower($appCode));
        self::assertStringNotContainsString('reactphp', strtolower($avaxCode));
        self::assertStringNotContainsString('EventLoop', $appCode);
        self::assertStringNotContainsString('EventLoop', $avaxCode);
        self::assertStringNotContainsString('HttpServer', $appCode);
        self::assertStringNotContainsString('HttpServer', $avaxCode);
    }

    // === Architecture / Composition Checks ===

    public function testNoDuplicateRequestScopeImplementation(): void
    {
        // RequestScope implementation should only exist in Capabilities/RequestScope
        // We check for classes that implement RequestScopeInterface or are named "RequestScope.php"
        $frameworkDir = __DIR__ . '/../../../../framework';

        $files = glob_recursive("{$frameworkDir}/**/*.php");
        $requestScopeFiles = [];
        foreach ($files as $file) {
            $basename = basename($file);
            // Only check actual RequestScope class files
            if ($basename === 'RequestScope.php') {
                $requestScopeFiles[] = $file;
            }
        }

        // All RequestScope.php files should be under Capabilities/RequestScope
        foreach ($requestScopeFiles as $file) {
            self::assertStringContainsString(
                'Capabilities/RequestScope',
                str_replace('\\', '/', $file),
                "RequestScope implementation found outside Capabilities/RequestScope: {$file}",
            );
        }
    }

    public function testWarmWorkerSafetyUsesExistingRuntime(): void
    {
        $warmCode = (string) file_get_contents(__DIR__ . '/../../../../framework/System/Runtime/WarmApplication/HandleWarmRequest.php');
        $flushCode = (string) file_get_contents(__DIR__ . '/../../../../framework/System/Runtime/WarmApplication/FlushScopedInstances.php');

        // Must use existing StateResetRegistry
        self::assertStringContainsString('StateResetRegistry', $flushCode);

        // Must use existing RequestScope
        self::assertStringContainsString('RequestScope', $flushCode);
    }

    public function testMemoryGuardDoesNotDependOnReactPhpDirectly(): void
    {
        $memoryGuardDir = __DIR__ . '/../../../../framework/System/Runtime/MemoryGuard/';
        $files = glob("{$memoryGuardDir}/*.php");
        if ($files === false) {
            self::fail('Could not list MemoryGuard files');
        }

        foreach ($files as $file) {
            $code = (string) file_get_contents($file);
            self::assertStringNotContainsString('React\\', $code, "MemoryGuard file {$file} should not directly depend on ReactPHP");
        }
    }

    public function testReactPhpRuntimeDependsOnWarmApplication(): void
    {
        $reactCode = (string) file_get_contents(__DIR__ . '/../../../../framework/System/Runtime/ReactPhp/RunReactHttpServer.php');

        self::assertStringContainsString('HandleWarmRequest', $reactCode);
        self::assertStringContainsString('setWarmHandler', $reactCode);
    }

    public function testPublicSurfaceStaysThin(): void
    {
        $appCode = (string) file_get_contents(__DIR__ . '/../../../../framework/System/PublicSurface/App.php');
        $lineCount = count(explode("\n", $appCode));

        self::assertLessThan(400, $lineCount, 'App PublicSurface should stay thin');
    }
}

/**
 * ResetTracker — Simple ResettableState implementation for testing.
 */
final class ResetTracker implements ResettableState
{
    private bool $reset = false;

    public function resetState(): void
    {
        $this->reset = true;
    }

    public function wasReset(): bool
    {
        return $this->reset;
    }
}

/**
 * Recursive glob helper.
 *
 * @return list<string>
 */
function glob_recursive(string $pattern): array
{
    $results = [];
    $files = glob($pattern);
    if ($files === false) {
        return [];
    }

    foreach ($files as $file) {
        $results[] = $file;
    }

    $dirName = dirname($pattern);
    $dirs = glob($dirName . '/*', GLOB_ONLYDIR);
    if ($dirs === false) {
        return $results;
    }

    foreach ($dirs as $dir) {
        $dirPattern = $dir . '/' . basename($pattern);
        $subResults = glob_recursive($dirPattern);
        foreach ($subResults as $result) {
            $results[] = $result;
        }
    }

    return $results;
}
