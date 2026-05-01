<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Monitoring\System\Capabilities\Metrics;

final class MetricsRegistry
{
    /** @var array<string, float> */
    private array $counters = [];

    /** @var array<string, list<float>> */
    private array $timings = [];

    public function increment(string $name, float $by = 1.0): void
    {
        $this->counters[$name] = ($this->counters[$name] ?? 0.0) + $by;
    }

    public function timing(string $name, float $milliseconds): void
    {
        $this->timings[$name][] = $milliseconds;
    }

    public function snapshot(): array
    {
        return [
            'counters' => $this->counters,
            'timings' => array_map(
                callback: static fn (array $values): array => [
                    'count' => count($values),
                    'avg_ms' => $values === [] ? 0.0 : array_sum(array: $values) / count($values),
                    'max_ms' => $values === [] ? 0.0 : max($values),
                ],
                array   : $this->timings,
            ),
        ];
    }

    public function prometheus(): string
    {
        $lines = [];

        foreach ($this->counters as $name => $value) {
            $lines[] = $this->normalize(name: $name) . ' ' . $value;
        }

        return implode(separator: "\n", array: $lines);
    }

    private function normalize(string $name): string
    {
        return preg_replace(pattern: '/[^a-zA-Z0-9_:]/', replacement: '_', subject: $name) ?? $name;
    }
}
