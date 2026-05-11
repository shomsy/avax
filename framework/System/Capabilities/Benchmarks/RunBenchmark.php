<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Benchmarks;

use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkResult;
use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkSuite;
use Throwable;

final class RunBenchmark
{
    /**
     * Run a benchmark with the given callback.
     *
     * @param callable(): void $work
     */
    public function run(
        string $name,
        callable $work,
        int $iterations = 1000,
        int $warmupIterations = 10,
    ): BenchmarkResult {
        $memoryBefore = memory_get_usage(true);
        $memoryPeakBefore = memory_get_peak_usage(true);

        // Warmup
        $actualWarmup = min($warmupIterations, $iterations);
        for ($i = 0; $i < $actualWarmup; $i++) {
            $work();
        }

        $times = [];
        $errors = 0;
        $totalStart = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            try {
                $start = microtime(true);
                $work();
                $times[] = (microtime(true) - $start) * 1000;
            } catch (Throwable) {
                $errors++;
                $times[] = 0.0;
            }
        }

        $totalSeconds = microtime(true) - $totalStart;

        sort($times);

        $memoryAfter = memory_get_usage(true);
        $memoryPeakAfter = max($memoryPeakBefore, memory_get_peak_usage(true));

        return BenchmarkResult::fromTimes(
            name: $name,
            iterations: $iterations,
            warmupIterations: $actualWarmup,
            totalSeconds: $totalSeconds,
            sortedTimes: $times,
            memoryBeforeBytes: $memoryBefore,
            memoryAfterBytes: $memoryAfter,
            memoryPeakBytes: $memoryPeakAfter,
            errors: $errors,
        );
    }

    /**
     * Run multiple benchmarks as a suite.
     *
     * @param array<string, callable(): void> $workloads
     */
    public function runSuite(
        string $name,
        array $workloads,
        int $iterations = 1000,
        int $warmupIterations = 10,
    ): BenchmarkSuite {
        $results = [];

        foreach ($workloads as $benchName => $work) {
            $results[] = $this->run("{$name}/{$benchName}", $work, $iterations, $warmupIterations);
        }

        return new BenchmarkSuite(
            name: $name,
            results: $results,
            environment: phpversion(),
            timestamp: date('c'),
        );
    }
}
