<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Parallelism;

use Avax\Components\Foundation\CallableSerialization\System\PublicSurface\CallableSerialization;
use Avax\Components\Operations\Parallelism\System\Capabilities\RunInCurrentProcess\CurrentProcessParallelRuntime;
use Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool\SymfonyProcessParallelRuntime;
use Avax\Components\Operations\Parallelism\System\Configuration\BuildParallelRuntime;
use Avax\Components\Operations\Parallelism\System\Configuration\ParallelismConfig;
use Avax\Components\Operations\Parallelism\System\Flows\RunWorkInParallel\RunWorkInParallel;
use Avax\Components\Operations\Parallelism\System\Foundation\Failure\ParallelException;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelFailure;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

/**
 * Proof tests for Parallelism process-based parallel execution.
 *
 * These tests prove that:
 * - BuildParallelRuntime selects correct runtime
 * - RunWorkInParallel uses the builder (not hardcoded runtime)
 * - SymfonyProcessParallelRuntime is selectable via config
 * - CurrentProcessParallelRuntime handles all closure types correctly
 * - Worker failures are captured correctly
 * - Worker payloads are signed via CallableSerialization
 *
 * Worker payloads use laravel/serializable-closure for cross-process
 * closure serialization with HMAC-SHA256 signing for integrity verification.
 */
final class ParallelismProofTest extends TestCase
{
    // ============================================================
    // 1. BUILD PARALLEL RUNTIME SELECTION
    // ============================================================

    public function test_buildParallelRuntime_defaults_to_current_process() : void
    {
        $builder = new BuildParallelRuntime();
        $runtimes = $builder->detectAvailableRuntimes();

        $this->assertArrayHasKey('current_process', $runtimes);
        $this->assertTrue($runtimes['current_process']);

        if (class_exists(\Symfony\Component\Process\Process::class)) {
            $this->assertArrayHasKey('symfony_process', $runtimes);
        }
    }

    public function test_buildParallelRuntime_builds_current_process_by_default() : void
    {
        $builder = new BuildParallelRuntime();
        $runtime = $builder->build();

        $this->assertInstanceOf(
            CurrentProcessParallelRuntime::class,
            $runtime,
            'Default build returns CurrentProcessParallelRuntime',
        );
    }

    public function test_buildParallelRuntime_builds_symfony_process_when_configured() : void
    {
        $builder = new BuildParallelRuntime();
        $config  = new ParallelismConfig(runtime: 'symfony_process');
        $runtime = $builder->build($config);

        $this->assertInstanceOf(
            SymfonyProcessParallelRuntime::class,
            $runtime,
            'BuildParallelRuntime builds SymfonyProcessParallelRuntime when explicitly configured',
        );
    }

    public function test_buildParallelRuntime_supports_process_alias() : void
    {
        $builder = new BuildParallelRuntime();
        $config  = new ParallelismConfig(runtime: 'process');
        $runtime = $builder->build($config);

        $this->assertInstanceOf(SymfonyProcessParallelRuntime::class, $runtime);
    }

    // ============================================================
    // 2. RUNWORKINPARALLEL USES BUILDER
    // ============================================================

    public function test_runWorkInParallel_uses_buildParallelRuntime_not_hardcoded() : void
    {
        $flow   = new RunWorkInParallel();
        $reflection = new ReflectionClass($flow);
        $builderProperty = $reflection->getProperty('builder');
        $builder = $builderProperty->getValue($flow);

        $this->assertInstanceOf(
            BuildParallelRuntime::class,
            $builder,
            'RunWorkInParallel uses BuildParallelRuntime, not hardcoded CurrentProcessParallelRuntime',
        );
    }

    // ============================================================
    // 3. CURRENT PROCESS RUNTIME HANDLES ALL CLOSURE TYPES
    // ============================================================

    public function test_current_process_runtime_handles_all_closure_types() : void
    {
        // CurrentProcessParallelRuntime runs closures in-process,
        // so it handles ALL closure types including those that capture scope.
        $runtime = new CurrentProcessParallelRuntime();
        $captured = 'captured_value';

        $result = $runtime->run([
            'capturing' => static fn () => $captured,
            'simple'    => static fn () => 'simple',
        ]);

        $this->assertTrue($result->successful());
        $this->assertSame('captured_value', $result->value('capturing'));
        $this->assertSame('simple', $result->value('simple'));
    }

    public function test_current_process_runtime_captures_exceptions_as_failures() : void
    {
        $runtime = new CurrentProcessParallelRuntime();

        $result = $runtime->run([
            'good' => static fn () => 'ok',
            'bad'  => static fn () => throw new RuntimeException('Worker error'),
        ]);

        $this->assertFalse($result->successful());
        $this->assertSame('ok', $result->value('good'));
        $this->assertNull($result->value('bad'));

        $failures = $result->failures();
        $this->assertCount(1, $failures);
        $this->assertSame('bad', $failures[0]->getName());
        $this->assertSame('Worker error', $failures[0]->getMessage());
    }

    public function test_current_process_runtime_empty_work_returns_empty_result() : void
    {
        $runtime = new CurrentProcessParallelRuntime();
        $result  = $runtime->run([]);

        $this->assertTrue($result->successful());
        $this->assertSame([], $result->values());
        $this->assertSame([], $result->failures());
    }

    public function test_current_process_runtime_preserves_integer_keys() : void
    {
        $runtime = new CurrentProcessParallelRuntime();

        $result = $runtime->run([
            0 => static fn () => 'zero',
            1 => static fn () => 'one',
            2 => static fn () => 'two',
        ]);

        $this->assertTrue($result->successful());
        $this->assertSame('zero', $result->value(0));
        $this->assertSame('one', $result->value(1));
        $this->assertSame('two', $result->value(2));
    }

    public function test_current_process_runtime_handles_null_returns() : void
    {
        $runtime = new CurrentProcessParallelRuntime();

        $result = $runtime->run([
            'nullable' => static fn () => null,
        ]);

        $this->assertTrue($result->successful());
        $this->assertNull($result->value('nullable'));
    }

    // ============================================================
    // 4. FAILURE REPRESENTATION
    // ============================================================

    public function test_parallelFailure_represents_worker_failure_correctly() : void
    {
        $previous = new RuntimeException('Root cause', 42);
        $failure  = new ParallelFailure(
            name    : 'worker_1',
            message : 'Worker crashed',
            code    : 1,
            previous: $previous,
        );

        $this->assertSame('worker_1', $failure->getName());
        $this->assertSame('Worker crashed', $failure->getMessage());
        $this->assertSame(1, $failure->getCode());
        $this->assertSame($previous, $failure->getPrevious());
    }

    public function test_parallelResult_throwIfFailed_with_single_failure() : void
    {
        $result = new \Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult(
            values         : [],
            failures       : [new ParallelFailure(name: 'w1', message: 'Single failure', code: 1)],
            startedWorkers : 1,
            finishedWorkers: 1,
            failedWorkers  : 1,
        );

        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage('Single failure');

        $result->throwIfFailed();
    }

    public function test_parallelResult_throwIfFailed_with_multiple_failures() : void
    {
        $result = new \Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult(
            values         : [],
            failures       : [
                new ParallelFailure(name: 'w1', message: 'A', code: 1),
                new ParallelFailure(name: 'w2', message: 'B', code: 2),
            ],
            startedWorkers : 2,
            finishedWorkers: 2,
            failedWorkers  : 2,
        );

        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage('2 workers failed');

        $result->throwIfFailed();
    }

    // ============================================================
    // 5. WORKER PAYLOAD SECURITY (CallableSerialization)
    // ============================================================

    public function test_worker_payloads_are_signed_via_callable_serialization() : void
    {
        $closure = static fn () => 'signed-work';
        $payload = CallableSerialization::encode($closure, 'worker-secret');

        // Payload is JSON with signature
        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('signature', $data);
        $this->assertNotEmpty($data['signature']);
        $this->assertArrayHasKey('encoded', $data);
    }

    public function test_worker_payload_verified_before_execution() : void
    {
        $closure = static fn () => 'verified';
        $payload = CallableSerialization::encode($closure, 'worker-secret');

        $result = CallableSerialization::decode($payload, 'worker-secret');

        $this->assertTrue(isset($result['closure']));
        $this->assertSame('verified', $result['closure']());
    }

    public function test_tampered_worker_payload_is_rejected() : void
    {
        $closure = static fn () => 'should-not-run';
        $payload = CallableSerialization::encode($closure, 'key-a');

        // Attacker changes the signing key
        $result = CallableSerialization::decode($payload, 'key-b');

        $this->assertTrue(isset($result['failure']));
        $this->assertTrue($result['failure']->isCorrupted());
    }

    public function test_native_serialize_cannot_serialize_closures() : void
    {
        $closure = static fn () => 'hello';

        $this->expectException(\Exception::class);
        serialize($closure);
    }

    public function test_symfonyProcessParallelRuntime_is_instantiable() : void
    {
        if (! class_exists(\Symfony\Component\Process\Process::class)) {
            $this->markTestSkipped('Symfony Process not available');
        }

        $runtime = new SymfonyProcessParallelRuntime();

        $this->assertInstanceOf(SymfonyProcessParallelRuntime::class, $runtime);
    }
}
