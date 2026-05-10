<?php

declare(strict_types=1);

namespace Tests\Unit\Framework\V4Benchmarks;

use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkResult;
use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkSuite;
use Avax\Framework\System\Capabilities\Benchmarks\RunBenchmark;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class V4BenchmarksTest extends TestCase
{
    #[Test]
    public function benchmark_runs_and_produces_sane_results(): void
    {
        $runner = new RunBenchmark();

        $result = $runner->run('noop', static fn () => null, 100);

        self::assertSame('noop', $result->name);
        self::assertSame(100, $result->iterations);
        self::assertGreaterThan(0, $result->totalSeconds);
        self::assertGreaterThanOrEqual(0, $result->avgMs);
        self::assertGreaterThanOrEqual(0, $result->minMs);
        self::assertGreaterThanOrEqual(0, $result->maxMs);
    }

    #[Test]
    public function benchmark_suite_runs_multiple(): void
    {
        $runner = new RunBenchmark();

        $suite = $runner->runSuite('test', [
            'noop' => static fn () => null,
            'sleep' => static fn () => usleep(10),
        ], 50);

        self::assertCount(2, $suite->results);
    }

    #[Test]
    public function benchmark_result_to_array(): void
    {
        $result = new BenchmarkResult('test', 100, 0.1, 1.0, 0.5, 2.0, ['p95' => 1.5]);

        $array = $result->toArray();

        self::assertSame('test', $array['name']);
        self::assertSame(100, $array['iterations']);
        self::assertArrayHasKey('avg_ms', $array);
        self::assertArrayHasKey('min_ms', $array);
        self::assertArrayHasKey('max_ms', $array);
    }

    #[Test]
    public function benchmark_suite_to_array(): void
    {
        $result = new BenchmarkResult('test', 100, 0.1, 1.0, 0.5, 2.0);
        $suite = new BenchmarkSuite('suite', [$result], '8.5', '2026-05-10');

        $array = $suite->toArray();

        self::assertSame('suite', $array['suite']);
        self::assertSame('8.5', $array['environment']);
        self::assertCount(1, $array['results']);
    }
}
