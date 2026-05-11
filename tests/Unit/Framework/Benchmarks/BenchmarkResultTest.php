<?php

declare(strict_types=1);

namespace Tests\Unit\Framework\Benchmarks;

use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BenchmarkResultTest extends TestCase
{
    #[Test]
    public function constructor_creates_result_with_all_properties(): void
    {
        $result = new BenchmarkResult(
            name: 'test_bench',
            iterations: 100,
            warmupIterations: 10,
            totalSeconds: 1.5,
            avgMs: 15.0,
            minMs: 5.0,
            maxMs: 25.0,
            p50Ms: 14.0,
            p95Ms: 20.0,
            p99Ms: 24.0,
            rps: 66.67,
            memoryBeforeBytes: 1048576,
            memoryAfterBytes: 1048600,
            memoryPeakBytes: 1100000,
            errorRate: 0.0,
            status: 'GREEN',
            notes: ['test note'],
        );

        self::assertSame('test_bench', $result->name);
        self::assertSame(100, $result->iterations);
        self::assertSame(10, $result->warmupIterations);
        self::assertSame(1.5, $result->totalSeconds);
        self::assertSame(15.0, $result->avgMs);
        self::assertSame('GREEN', $result->status);
        self::assertSame(['test note'], $result->notes);
    }

    #[Test]
    public function fromTimes_computes_correct_metrics(): void
    {
        $times = [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0];
        $totalSeconds = 0.055;

        $result = BenchmarkResult::fromTimes(
            name: 'compute_test',
            iterations: 10,
            warmupIterations: 2,
            totalSeconds: $totalSeconds,
            sortedTimes: $times,
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000100,
            memoryPeakBytes: 1000200,
            errors: 0,
        );

        self::assertSame('compute_test', $result->name);
        self::assertSame(10, $result->iterations);
        self::assertSame(5.5, $result->avgMs);
        self::assertSame(1.0, $result->minMs);
        self::assertSame(10.0, $result->maxMs);
        // floor(10*0.50)=5, times[5]=6.0 (0-indexed)
        self::assertSame(6.0, $result->p50Ms);
        // floor(10*0.95)=9, times[9]=10.0
        self::assertSame(10.0, $result->p95Ms);
        // floor(10*0.99)=9, times[9]=10.0
        self::assertSame(10.0, $result->p99Ms);
        self::assertGreaterThan(0, $result->rps);
        self::assertSame(0.0, $result->errorRate);
        self::assertSame('GREEN', $result->status);
    }

    #[Test]
    public function fromTimes_handles_zero_iterations(): void
    {
        $result = BenchmarkResult::fromTimes(
            name: 'empty',
            iterations: 0,
            warmupIterations: 0,
            totalSeconds: 0.0,
            sortedTimes: [],
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000000,
            memoryPeakBytes: 1000000,
        );

        self::assertSame('empty', $result->name);
        self::assertSame(0, $result->iterations);
        self::assertSame(0.0, $result->avgMs);
        self::assertSame(0.0, $result->minMs);
        self::assertSame(0.0, $result->maxMs);
        self::assertSame(0.0, $result->p50Ms);
        self::assertSame(0.0, $result->p95Ms);
        self::assertSame(0.0, $result->p99Ms);
        self::assertSame(0.0, $result->rps);
        self::assertSame(0.0, $result->errorRate);
    }

    #[Test]
    public function fromTimes_handles_all_errors(): void
    {
        $result = BenchmarkResult::fromTimes(
            name: 'all_errors',
            iterations: 100,
            warmupIterations: 10,
            totalSeconds: 0.5,
            sortedTimes: array_fill(0, 100, 0.0),
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000000,
            memoryPeakBytes: 1000000,
            errors: 100,
            notes: ['All iterations failed'],
        );

        self::assertSame(1.0, $result->errorRate);
        self::assertSame(['All iterations failed'], $result->notes);
    }

    #[Test]
    public function fromTimes_handles_single_iteration(): void
    {
        $result = BenchmarkResult::fromTimes(
            name: 'single',
            iterations: 1,
            warmupIterations: 0,
            totalSeconds: 0.001,
            sortedTimes: [1.0],
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000050,
            memoryPeakBytes: 1000100,
        );

        self::assertSame(1, $result->iterations);
        self::assertSame(1.0, $result->avgMs);
        self::assertSame(1.0, $result->minMs);
        self::assertSame(1.0, $result->maxMs);
        self::assertSame(1.0, $result->p50Ms);
        self::assertSame(1.0, $result->p95Ms);
        self::assertSame(1.0, $result->p99Ms);
        self::assertSame(1000.0, $result->rps);
    }

    #[Test]
    public function fromTimes_accepts_custom_status(): void
    {
        $result = BenchmarkResult::fromTimes(
            name: 'leaky',
            iterations: 1000,
            warmupIterations: 100,
            totalSeconds: 1.0,
            sortedTimes: array_fill(0, 1000, 1.0),
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 12000000,
            memoryPeakBytes: 12000000,
            errors: 0,
            notes: ['Memory growth > 10MB'],
            status: 'RED',
        );

        self::assertSame('RED', $result->status);
    }

    #[Test]
    public function fromTimes_accepts_yellow_status(): void
    {
        $result = BenchmarkResult::fromTimes(
            name: 'suspicious',
            iterations: 1000,
            warmupIterations: 100,
            totalSeconds: 1.0,
            sortedTimes: array_fill(0, 1000, 1.0),
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 2500000,
            memoryPeakBytes: 2500000,
            errors: 0,
            notes: ['Memory growth > 1MB'],
            status: 'YELLOW',
        );

        self::assertSame('YELLOW', $result->status);
    }

    #[Test]
    public function to_array_returns_canonical_structure(): void
    {
        $result = new BenchmarkResult(
            name: 'test',
            iterations: 500,
            warmupIterations: 50,
            totalSeconds: 2.5,
            avgMs: 5.0,
            minMs: 1.0,
            maxMs: 10.0,
            p50Ms: 4.5,
            p95Ms: 8.0,
            p99Ms: 9.5,
            rps: 200.0,
            memoryBeforeBytes: 1048576,
            memoryAfterBytes: 1049000,
            memoryPeakBytes: 1100000,
            errorRate: 0.01,
            status: 'GREEN',
            notes: ['test note'],
        );

        $array = $result->toArray();

        self::assertSame('test', $array['name']);
        self::assertSame(500, $array['iterations']);
        self::assertSame(50, $array['warmup_iterations']);
        self::assertSame(2.5, $array['total_seconds']);
        self::assertArrayHasKey('metrics', $array);
        self::assertSame(5.0, $array['metrics']['avg_ms']);
        self::assertSame(4.5, $array['metrics']['p50_ms']);
        self::assertSame(8.0, $array['metrics']['p95_ms']);
        self::assertSame(9.5, $array['metrics']['p99_ms']);
        self::assertSame(1.0, $array['metrics']['min_ms']);
        self::assertSame(10.0, $array['metrics']['max_ms']);
        self::assertSame(200.0, $array['metrics']['rps']);
        self::assertSame(1048576, $array['metrics']['memory_before_bytes']);
        self::assertSame(1049000, $array['metrics']['memory_after_bytes']);
        self::assertSame(1100000, $array['metrics']['memory_peak_bytes']);
        self::assertSame(0.01, $array['metrics']['error_rate']);
        self::assertSame('GREEN', $array['status']);
        self::assertSame(['test note'], $array['notes']);
    }

    #[Test]
    public function to_array_rounds_values_correctly(): void
    {
        $result = new BenchmarkResult(
            name: 'precision_test',
            iterations: 1,
            warmupIterations: 0,
            totalSeconds: 0.123456789,
            avgMs: 1.23456789,
            minMs: 0.123456789,
            maxMs: 9.87654321,
            p50Ms: 1.11111111,
            p95Ms: 2.22222222,
            p99Ms: 3.33333333,
            rps: 123.456789,
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000100,
            memoryPeakBytes: 1000200,
            errorRate: 0.00123456,
        );

        $array = $result->toArray();

        self::assertSame(0.1235, $array['total_seconds']);
        self::assertSame(1.2346, $array['metrics']['avg_ms']);
        self::assertSame(0.001235, $array['metrics']['error_rate']);
    }

    #[Test]
    public function to_canonical_format_includes_environment_metadata(): void
    {
        $result = new BenchmarkResult(
            name: 'bench',
            iterations: 100,
            warmupIterations: 10,
            totalSeconds: 1.0,
            avgMs: 10.0,
            minMs: 5.0,
            maxMs: 20.0,
            p50Ms: 10.0,
            p95Ms: 15.0,
            p99Ms: 18.0,
            rps: 100.0,
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000100,
            memoryPeakBytes: 1000200,
            errorRate: 0.0,
        );

        $canonical = $result->toCanonicalFormat(
            stage: 'V5.5-03',
            environmentId: 'local-dev',
            gitCommit: 'abc123',
            phpVersion: '8.5.0',
            opcache: 'enabled',
            jit: 'disabled',
            runtime: 'cli',
            concurrency: 1,
        );

        self::assertSame('V5.5-03', $canonical['stage']);
        self::assertSame('bench', $canonical['benchmark']);
        self::assertSame('local-dev', $canonical['environment_id']);
        self::assertSame('abc123', $canonical['git_commit']);
        self::assertSame('8.5.0', $canonical['php_version']);
        self::assertSame('enabled', $canonical['opcache']);
        self::assertSame('disabled', $canonical['jit']);
        self::assertSame('cli', $canonical['runtime']);
        self::assertSame(1, $canonical['concurrency']);
        self::assertArrayHasKey('metrics', $canonical);
        self::assertArrayHasKey('status', $canonical);
        self::assertArrayHasKey('notes', $canonical);
    }

    #[Test]
    public function to_canonical_format_completeness(): void
    {
        $result = new BenchmarkResult(
            name: 'complete_test',
            iterations: 200,
            warmupIterations: 20,
            totalSeconds: 3.0,
            avgMs: 15.0,
            minMs: 5.0,
            maxMs: 25.0,
            p50Ms: 14.0,
            p95Ms: 20.0,
            p99Ms: 23.0,
            rps: 66.67,
            memoryBeforeBytes: 2000000,
            memoryAfterBytes: 2001000,
            memoryPeakBytes: 2100000,
            errorRate: 0.005,
            status: 'GREEN',
            notes: ['verified'],
        );

        $canonical = $result->toCanonicalFormat(
            stage: 'V5.5-01',
            environmentId: 'ci',
            gitCommit: 'def456',
            phpVersion: '8.5.5',
            opcache: 'enabled',
            jit: 'disable',
            runtime: 'fpm',
        );

        $requiredKeys = [
            'stage', 'benchmark', 'environment_id', 'timestamp', 'git_commit',
            'php_version', 'opcache', 'jit', 'runtime', 'iterations',
            'warmup_iterations', 'concurrency', 'metrics', 'status', 'notes',
        ];
        foreach ($requiredKeys as $key) {
            self::assertArrayHasKey($key, $canonical, "Missing key: {$key}");
        }

        $metricsKeys = [
            'avg_ms', 'p50_ms', 'p95_ms', 'p99_ms', 'min_ms', 'max_ms',
            'rps', 'memory_before_bytes', 'memory_after_bytes', 'memory_peak_bytes', 'error_rate',
        ];
        foreach ($metricsKeys as $key) {
            self::assertArrayHasKey($key, $canonical['metrics'], "Missing metrics key: {$key}");
        }
    }

    #[Test]
    public function large_percentile_array_computes_correctly(): void
    {
        $times = array_fill(0, 1000, 1.0);
        $times[998] = 50.0;
        $times[999] = 100.0;
        sort($times);

        $result = BenchmarkResult::fromTimes(
            name: 'large_percentile',
            iterations: 1000,
            warmupIterations: 100,
            totalSeconds: 1.5,
            sortedTimes: $times,
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000100,
            memoryPeakBytes: 1000200,
        );

        // floor(1000*0.50)=500, times[500]=1.0
        self::assertSame(1.0, $result->p50Ms);
        // floor(1000*0.95)=950, times[950]=1.0
        self::assertSame(1.0, $result->p95Ms);
        // floor(1000*0.99)=990, times[990]=1.0 (only indices 998,999 are outliers)
        self::assertSame(1.0, $result->p99Ms);
        self::assertSame(100.0, $result->maxMs);
    }

    #[Test]
    public function error_rate_classification(): void
    {
        $result = BenchmarkResult::fromTimes(
            name: 'half_errors',
            iterations: 100,
            warmupIterations: 10,
            totalSeconds: 1.0,
            sortedTimes: array_fill(0, 100, 1.0),
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000000,
            memoryPeakBytes: 1000000,
            errors: 50,
        );

        self::assertSame(0.5, $result->errorRate);
    }

    #[Test]
    public function readonly_class_is_immutable(): void
    {
        $result = new BenchmarkResult(
            name: 'immutable',
            iterations: 1,
            warmupIterations: 0,
            totalSeconds: 0.1,
            avgMs: 100.0,
            minMs: 100.0,
            maxMs: 100.0,
            p50Ms: 100.0,
            p95Ms: 100.0,
            p99Ms: 100.0,
            rps: 10.0,
            memoryBeforeBytes: 1000000,
            memoryAfterBytes: 1000000,
            memoryPeakBytes: 1000000,
            errorRate: 0.0,
        );

        $array1 = $result->toArray();
        $array1['name'] = 'mutated';
        $array2 = $result->toArray();
        self::assertSame('immutable', $array2['name']);
    }
}
