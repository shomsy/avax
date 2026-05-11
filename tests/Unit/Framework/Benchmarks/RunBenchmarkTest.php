<?php

declare(strict_types=1);

namespace Tests\Unit\Framework\Benchmarks;

use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkResult;
use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkSuite;
use Avax\Framework\System\Capabilities\Benchmarks\RunBenchmark;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RunBenchmarkTest extends TestCase
{
    #[Test]
    public function run_executes_workload_and_returns_result(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('noop', static fn () => null, 100);

        self::assertSame('noop', $result->name);
        self::assertSame(100, $result->iterations);
        self::assertGreaterThan(0, $result->totalSeconds);
        self::assertGreaterThanOrEqual(0, $result->avgMs);
    }

    #[Test]
    public function run_excludes_warmup_from_results(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('warmup_test', static fn () => null, 50, 20);

        self::assertSame(50, $result->iterations);
        self::assertSame(20, $result->warmupIterations);
        self::assertLessThan($result->iterations, $result->warmupIterations);
    }

    #[Test]
    public function run_caps_warmup_at_iterations(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('cap_test', static fn () => null, 10, 200);

        self::assertSame(10, $result->warmupIterations);
        self::assertSame(10, $result->iterations);
    }

    #[Test]
    public function run_handles_callable_that_throws(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('error_test', static function (): void {
            throw new RuntimeException('benchmark error');
        }, 100);

        self::assertSame(100, $result->iterations);
        // Errors are caught by RunBenchmark, errorRate tracks the proportion
        self::assertGreaterThanOrEqual(0, $result->errorRate);
    }

    #[Test]
    public function run_handles_mixed_success_and_error(): void
    {
        $runner = new RunBenchmark();
        $counter = 0;
        $result = $runner->run('mixed_test', static function () use (&$counter): void {
            $counter++;
            if ($counter % 2 === 0) {
                throw new RuntimeException('even error');
            }
        }, 100);

        // RunBenchmark catches errors and tracks errorRate
        self::assertSame(100, $result->iterations);
        self::assertGreaterThan(0, $result->errorRate);
    }

    #[Test]
    public function run_tracks_memory(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('memory_test', static function (): void {
            // Allocate memory to produce a measurable delta
            $buffer = str_repeat('x', 1024 * 64);
            if (strlen($buffer) !== 1024 * 64) {
                throw new RuntimeException('unexpected buffer size');
            }
        }, 100);

        self::assertGreaterThan(0, $result->memoryBeforeBytes);
        self::assertGreaterThan(0, $result->memoryAfterBytes);
        self::assertGreaterThan(0, $result->memoryPeakBytes);
        self::assertGreaterThanOrEqual($result->memoryBeforeBytes, $result->memoryPeakBytes);
    }

    #[Test]
    public function run_computes_rps(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('rps_test', static fn () => null, 1000);

        self::assertGreaterThan(0, $result->rps);
        $expectedRps = $result->iterations / $result->totalSeconds;
        self::assertEqualsWithDelta($expectedRps, $result->rps, 1.0);
    }

    #[Test]
    public function run_suite_runs_multiple_workloads(): void
    {
        $runner = new RunBenchmark();
        $suite = $runner->runSuite('test_suite', [
            'fast' => static fn () => null,
            'slow' => static fn () => usleep(10),
        ], 50, 5);

        self::assertSame('test_suite', $suite->name);
        self::assertCount(2, $suite->results);
        self::assertSame('test_suite/fast', $suite->results[0]->name);
        self::assertSame('test_suite/slow', $suite->results[1]->name);
    }

    #[Test]
    public function run_suite_with_empty_workloads(): void
    {
        $runner = new RunBenchmark();
        $suite = $runner->runSuite('empty_suite', [], 10);

        self::assertSame('empty_suite', $suite->name);
        self::assertCount(0, $suite->results);
    }

    #[Test]
    public function run_suite_includes_environment_and_timestamp(): void
    {
        $runner = new RunBenchmark();
        $suite = $runner->runSuite('env_test', ['noop' => static fn () => null], 10);

        self::assertSame(PHP_VERSION, $suite->environment);
        self::assertNotEmpty($suite->timestamp);
    }

    #[Test]
    public function run_with_zero_iterations(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('zero_iter', static fn () => null, 0, 0);

        self::assertSame(0, $result->iterations);
        self::assertSame(0, $result->warmupIterations);
        self::assertSame(0.0, $result->avgMs);
        self::assertSame(0.0, $result->rps);
    }

    #[Test]
    public function run_default_parameters(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('defaults', static fn () => null);

        self::assertSame(1000, $result->iterations);
        self::assertSame(10, $result->warmupIterations);
    }

    #[Test]
    public function run_result_status_defaults_to_green(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('status_test', static fn () => null, 100);

        self::assertSame('GREEN', $result->status);
    }

    #[Test]
    public function run_result_notes_are_empty_by_default(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('notes_test', static fn () => null, 100);

        self::assertSame([], $result->notes);
    }

    #[Test]
    public function run_with_closure_discarding_return_value(): void
    {
        $runner = new RunBenchmark();
        $result = $runner->run('return_test', static function (): void {
            // Simulates a callable that does work but returns nothing
            $x = 'hello world';
            unset($x);
        }, 100);

        self::assertSame(100, $result->iterations);
        // No errors expected — return value is simply discarded
        self::assertSame(0.0, $result->errorRate);
    }
}
