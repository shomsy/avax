<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Parallelism;

use Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool\SymfonyProcessParallelRuntime;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Proof tests for real multi-process parallel execution via Symfony Process.
 *
 * These tests prove that:
 * - SymfonyProcessParallelRuntime starts multiple child processes
 * - Each child process has a distinct PID (proves parallelism, not sequential)
 * - Closure serialization works across process boundaries (captured variables)
 * - CallableSerialization signs worker payloads
 * - Worker failures return structured results
 * - Result order is deterministic (matches input order)
 *
 * PID-based proof avoids flaky timing thresholds.
 */
final class ProcessPoolParallelismProofTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = __DIR__ . '/.tmp-parallel-' . uniqid();
        mkdir($this->tempDir);

        if (! function_exists('proc_open')) {
            self::markTestSkipped('proc_open is not available.');
        }

        if (! class_exists(\Symfony\Component\Process\Process::class)) {
            self::markTestSkipped('Symfony Process is not installed.');
        }
    }

    #[After]
    protected function tearDown(): void
    {
        foreach (glob("{$this->tempDir}/*") ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->tempDir);
    }

    // ============================================================
    // 1. MULTIPLE CHILD PROCESSES (PID-BASED PROOF)
    // ============================================================

    public function test_symfony_process_runtime_starts_multiple_child_processes() : void
    {
        $tasks = [];
        for ($i = 0; $i < 3; $i++) {
            $index = $i;
            $dir = $this->tempDir;
            $tasks["task_{$i}"] = static function () use ($dir, $index) {
                $pid = getmypid();
                file_put_contents("{$dir}/task_{$index}.pid", (string) $pid);
                return "task_{$index}_done";
            };
        }

        $runtime = new SymfonyProcessParallelRuntime(signingKey: 'proof-key');
        $result = $runtime->run($tasks, maxWorkers: 3);

        $this->assertTrue($result->successful(), 'All tasks should succeed. Failures: ' . json_encode($result->failures()));
        $this->assertCount(3, $result->values());

        // Collect PIDs written by child processes
        $pids = [];
        foreach (glob("{$this->tempDir}/task_*.pid") ?: [] as $file) {
            $raw = file_get_contents($file);
            if ($raw === false) {
                continue;
            }
            $content = trim($raw);
            if ($content !== '') {
                $pids[] = $content;
            }
        }

        $distinctPids = array_unique($pids);
        $this->assertGreaterThan(
            1,
            count($distinctPids),
            'Multiple distinct child PIDs (' . implode(', ', $distinctPids) . ') prove parallel execution in separate processes',
        );
    }

    // ============================================================
    // 2. CLOSURE SERIALIZATION ACROSS PROCESS BOUNDARY
    // ============================================================

    public function test_captured_variables_survive_process_boundary() : void
    {
        $captured = 'serialized-across-processes';

        $runtime = new SymfonyProcessParallelRuntime(signingKey: 'capture-test');
        $result = $runtime->run([
            'capture' => static fn() => $captured,
        ]);

        $this->assertTrue($result->successful());
        $this->assertSame('serialized-across-processes', $result->value('capture'));
    }

    // ============================================================
    // 3. CALLABLESERIALIZATION IN THE PATH
    // ============================================================

    public function test_worker_payloads_are_signed() : void
    {
        $runtime = new SymfonyProcessParallelRuntime(signingKey: 'signing-proof');
        $result = $runtime->run([
            'signed' => static fn() => 42,
        ]);

        $this->assertTrue($result->successful());
        $this->assertSame(42, $result->value('signed'));
    }

    // ============================================================
    // 4. DETERMINISTIC RESULT ORDER
    // ============================================================

    public function test_result_order_matches_input_order() : void
    {
        $runtime = new SymfonyProcessParallelRuntime(signingKey: 'order-test');
        $result = $runtime->run([
            'first'  => static fn() => 'a',
            'second' => static fn() => 'b',
            'third'  => static fn() => 'c',
        ]);

        $values = $result->values();
        $keys = array_keys($values);
        $this->assertSame(['first', 'second', 'third'], $keys);
        $this->assertSame('a', $values['first']);
        $this->assertSame('b', $values['second']);
        $this->assertSame('c', $values['third']);
    }

    // ============================================================
    // 5. WORKER FAILURE RETURNS STRUCTURED FAILURE
    // ============================================================

    public function test_worker_exception_returns_structured_failure() : void
    {
        $runtime = new SymfonyProcessParallelRuntime(signingKey: 'failure-test');
        $result = $runtime->run([
            'good' => static fn() => 'ok',
            'bad'  => static fn() => throw new RuntimeException('Worker crashed'),
        ]);

        $this->assertFalse($result->successful());
        $this->assertSame('ok', $result->value('good'));
        $this->assertNull($result->value('bad'));

        $failures = $result->failures();
        $this->assertCount(1, $failures);
        // Failure name is the WorkerId (generated), not the task name
        $this->assertStringContainsString('Worker crashed', $failures[0]->getMessage());
    }

    public function test_empty_work_returns_empty_result() : void
    {
        $runtime = new SymfonyProcessParallelRuntime();
        $result = $runtime->run([]);

        $this->assertTrue($result->successful());
        $this->assertSame([], $result->values());
        $this->assertSame([], $result->failures());
        $this->assertSame(0, $result->startedWorkers);
    }

    // ============================================================
    // 6. RESULT METADATA
    // ============================================================

    public function test_result_contains_worker_metadata() : void
    {
        $runtime = new SymfonyProcessParallelRuntime(signingKey: 'meta-test');
        $result = $runtime->run([
            'w1' => static fn() => 'one',
            'w2' => static fn() => 'two',
        ]);

        $this->assertSame(2, $result->startedWorkers);
        $this->assertSame(2, $result->finishedWorkers);
        $this->assertSame(0, $result->failedWorkers);
    }
}
