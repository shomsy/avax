<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Benchmarks\Foundation;

final readonly class BenchmarkSuite
{
    public function __construct(
        public string $name,
        /** @var list<BenchmarkResult> $results */
        public array $results = [],
        public string $environment = '',
        public string $timestamp = '',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'suite' => $this->name,
            'environment' => $this->environment,
            'timestamp' => $this->timestamp,
            'results' => array_map(
                static fn (BenchmarkResult $r) => $r->toArray(),
                $this->results,
            ),
        ];
    }
}
