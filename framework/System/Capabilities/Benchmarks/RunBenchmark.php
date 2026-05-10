<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Benchmarks;

use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkResult;
use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkSuite;

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
    ): BenchmarkResult {
        $times = [];

        // Warmup
        for ($i = 0; $i < min(10, $iterations); $i++) {
            $work();
        }

        $totalStart = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $work();
            $times[] = (microtime(true) - $start) * 1000;
        }

        $totalSeconds = microtime(true) - $totalStart;

        sort($times);

        $p95Index = (int) floor(count($times) * 0.95);
        $p95 = $times[$p95Index] ?? 0.0;

        return new BenchmarkResult(
            $name,
            $iterations,
            $totalSeconds,
            $times !== [] ? array_sum($times) / count($times) : 0.0,
            $times !== [] ? min($times) : 0.0,
            $times !== [] ? max($times) : 0.0,
            ['p95' => $p95],
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
    ): BenchmarkSuite {
        $results = [];

        foreach ($workloads as $benchName => $work) {
            $results[] = $this->run("{$name}/{$benchName}", $work, $iterations);
        }

        return new BenchmarkSuite(
            $name,
            $results,
            phpversion(),
            date('c'),
        );
    }
}
