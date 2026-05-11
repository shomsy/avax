<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Benchmarks;

use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkResult;
use Avax\Framework\System\Capabilities\Benchmarks\Foundation\BenchmarkSuite;
use stdClass;

final class MicroBenchmarkRunner
{
    private RunBenchmark $benchmark;

    public function __construct()
    {
        $this->benchmark = new RunBenchmark();
    }

    public function run(int $iterations = 1000, int $warmupIterations = 50): BenchmarkSuite
    {
        $results = [];

        $results[] = $this->benchmark->run(
            'array_map_1000',
            static function (): void {
                array_map(static fn ($v) => $v * 2, range(1, 100));
            },
            $iterations,
            $warmupIterations,
        );

        $results[] = $this->benchmark->run(
            'object_creation',
            static function (): void {
                new stdClass();
            },
            $iterations,
            $warmupIterations,
        );

        $results[] = $this->benchmark->run(
            'string_concat',
            static function (): void {
                'hello' . ' ' . 'world' . ' ' . time();
            },
            $iterations,
            $warmupIterations,
        );

        $results[] = $this->benchmark->run(
            'json_encode_decode',
            static function (): void {
                json_decode(json_encode(['a' => 1, 'b' => 2], JSON_THROW_ON_ERROR), true);
            },
            $iterations,
            $warmupIterations,
        );

        return new BenchmarkSuite('microbenchmarks', $results, PHP_VERSION, date('c'));
    }
}
