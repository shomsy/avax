<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Benchmarks\Foundation;

final readonly class BenchmarkResult
{
    public function __construct(
        public string $name,
        public int $iterations,
        public float $totalSeconds,
        public float $avgMs,
        public float $minMs,
        public float $maxMs,
        /** @var array<string, float> $percentiles */
        public array $percentiles = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'iterations' => $this->iterations,
            'total_seconds' => round($this->totalSeconds, 4),
            'avg_ms' => round($this->avgMs, 4),
            'min_ms' => round($this->minMs, 4),
            'max_ms' => round($this->maxMs, 4),
            'percentiles' => $this->percentiles,
        ];
    }
}
