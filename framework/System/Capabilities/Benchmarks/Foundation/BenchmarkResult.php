<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Benchmarks\Foundation;

final readonly class BenchmarkResult
{
    public function __construct(
        public string $name,
        public int $iterations,
        public int $warmupIterations,
        public float $totalSeconds,
        public float $avgMs,
        public float $minMs,
        public float $maxMs,
        public float $p50Ms,
        public float $p95Ms,
        public float $p99Ms,
        public float $rps,
        public int $memoryBeforeBytes,
        public int $memoryAfterBytes,
        public int $memoryPeakBytes,
        public float $errorRate,
        public string $status = 'GREEN',
        /** @var list<string> $notes */
        public array $notes = [],
    ) {
    }

    /**
     * @param list<float> $sortedTimes sorted ascending in milliseconds
     * @param list<string> $notes
     */
    public static function fromTimes(
        string $name,
        int $iterations,
        int $warmupIterations,
        float $totalSeconds,
        array $sortedTimes,
        int $memoryBeforeBytes,
        int $memoryAfterBytes,
        int $memoryPeakBytes,
        int $errors = 0,
        array $notes = [],
        string $status = 'GREEN',
    ): self {
        $count = count($sortedTimes);
        $avgMs = $count > 0 ? array_sum($sortedTimes) / $count : 0.0;
        $minMs = $count > 0 ? $sortedTimes[0] : 0.0;
        $maxMs = $count > 0 ? $sortedTimes[$count - 1] : 0.0;
        $p50Ms = $count > 0 ? $sortedTimes[(int) floor($count * 0.50)] : 0.0;
        $p95Ms = $count > 0 ? $sortedTimes[(int) floor($count * 0.95)] : 0.0;
        $p99Ms = $count > 0 ? $sortedTimes[(int) floor($count * 0.99)] : 0.0;
        $rps = $totalSeconds > 0 ? $iterations / $totalSeconds : 0.0;
        $errorRate = $iterations > 0 ? $errors / $iterations : 0.0;

        return new self(
            name: $name,
            iterations: $iterations,
            warmupIterations: $warmupIterations,
            totalSeconds: $totalSeconds,
            avgMs: $avgMs,
            minMs: $minMs,
            maxMs: $maxMs,
            p50Ms: $p50Ms,
            p95Ms: $p95Ms,
            p99Ms: $p99Ms,
            rps: $rps,
            memoryBeforeBytes: $memoryBeforeBytes,
            memoryAfterBytes: $memoryAfterBytes,
            memoryPeakBytes: $memoryPeakBytes,
            errorRate: $errorRate,
            status: $status,
            notes: $notes,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'iterations' => $this->iterations,
            'warmup_iterations' => $this->warmupIterations,
            'total_seconds' => round($this->totalSeconds, 4),
            'metrics' => [
                'avg_ms' => round($this->avgMs, 4),
                'p50_ms' => round($this->p50Ms, 4),
                'p95_ms' => round($this->p95Ms, 4),
                'p99_ms' => round($this->p99Ms, 4),
                'min_ms' => round($this->minMs, 4),
                'max_ms' => round($this->maxMs, 4),
                'rps' => round($this->rps, 2),
                'memory_before_bytes' => $this->memoryBeforeBytes,
                'memory_after_bytes' => $this->memoryAfterBytes,
                'memory_peak_bytes' => $this->memoryPeakBytes,
                'error_rate' => round($this->errorRate, 6),
            ],
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toCanonicalFormat(
        string $stage,
        string $environmentId,
        string $gitCommit,
        string $phpVersion,
        string $opcache,
        string $jit,
        string $runtime,
        int $concurrency = 1,
    ): array {
        return [
            'stage' => $stage,
            'benchmark' => $this->name,
            'environment_id' => $environmentId,
            'timestamp' => date('c'),
            'git_commit' => $gitCommit,
            'php_version' => $phpVersion,
            'opcache' => $opcache,
            'jit' => $jit,
            'runtime' => $runtime,
            'iterations' => $this->iterations,
            'warmup_iterations' => $this->warmupIterations,
            'concurrency' => $concurrency,
            'metrics' => [
                'avg_ms' => round($this->avgMs, 4),
                'p50_ms' => round($this->p50Ms, 4),
                'p95_ms' => round($this->p95Ms, 4),
                'p99_ms' => round($this->p99Ms, 4),
                'min_ms' => round($this->minMs, 4),
                'max_ms' => round($this->maxMs, 4),
                'rps' => round($this->rps, 2),
                'memory_before_bytes' => $this->memoryBeforeBytes,
                'memory_after_bytes' => $this->memoryAfterBytes,
                'memory_peak_bytes' => $this->memoryPeakBytes,
                'error_rate' => round($this->errorRate, 6),
            ],
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
