<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\InMemoryMetricExporter;

/**
 * In-memory metrics collector for testing and lightweight use.
 */
final class InMemoryMetricExporter
{
    /**
     * @var list<array{name: string, type: string, value: float, tags: array<string, string>, timestamp: float}>
     */
    private array $metrics = [];

    /**
     * Record a metric.
     *
     * @param array<string, string> $tags
     */
    public function record(string $name, string $type, float $value, array $tags = [], ?float $timestamp = null): void
    {
        $this->metrics[] = [
            'name' => $name,
            'type' => $type,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => $timestamp ?? microtime(true),
        ];
    }

    /**
     * @return list<array{name: string, type: string, value: float, tags: array<string, string>, timestamp: float}>
     */
    public function flush(): array
    {
        $metrics = $this->metrics;
        $this->metrics = [];

        return $metrics;
    }

    /**
     * @return list<array{name: string, type: string, value: float, tags: array<string, string>, timestamp: float}>
     */
    public function all(): array
    {
        return $this->metrics;
    }

    public function count(): int
    {
        return count($this->metrics);
    }
}
