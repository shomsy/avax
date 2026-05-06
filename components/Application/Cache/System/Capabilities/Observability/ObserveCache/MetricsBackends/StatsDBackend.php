<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends;

use Override;

final class StatsDBackend implements MetricsBackend
{
    /** @var array<string, int> */
    private array $counters = [];

    /** @var array<string, float> */
    private array $gauges = [];

    /** @var array<string, list<float>> */
    private array $histograms = [];

    /** @var array<string, list<int>> */
    private array $timings = [];

    /** @var list<string> */
    private array $messages = [];

    #[Override]
    public function increment(string $metric, int $value = 1): void
    {
        $key = sprintf('%s:%d|c', $metric, $value);
        $this->messages[] = $key;
        $this->counters[$metric] = ($this->counters[$metric] ?? 0) + $value;
    }

    #[Override]
    public function gauge(string $metric, float $value): void
    {
        $key = sprintf('%s:%s|g', $metric, $value);
        $this->messages[] = $key;
        $this->gauges[$metric] = $value;
    }

    #[Override]
    public function histogram(string $metric, float $value): void
    {
        $key = sprintf('%s:%s|h', $metric, $value);
        $this->messages[] = $key;

        if (! isset($this->histograms[$metric])) {
            $this->histograms[$metric] = [];
        }

        $this->histograms[$metric][] = $value;
    }

    #[Override]
    public function timing(string $metric, int $milliseconds): void
    {
        $key = sprintf('%s:%d|ms', $metric, $milliseconds);
        $this->messages[] = $key;

        if (! isset($this->timings[$metric])) {
            $this->timings[$metric] = [];
        }

        $this->timings[$metric][] = $milliseconds;
    }

    #[Override]
    public function flush(): void
    {
        $this->messages = [];
    }

    /**
     * @return list<string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return array<string, int>
     */
    public function getCounters(): array
    {
        return $this->counters;
    }

    /**
     * @return array<string, float>
     */
    public function getGauges(): array
    {
        return $this->gauges;
    }

    /**
     * @return array<string, list<int>>
     */
    public function getTimings(): array
    {
        return $this->timings;
    }

    /**
     * @return array<string, int>
     */
    public function getPercentile(float $percentile): array
    {
        $result = [];

        foreach ($this->timings as $metric => $values) {
            sort($values);
            $index = (int) ceil(($percentile / 100) * count($values)) - 1;
            $result[$metric] = $values[max(0, $index)] ?? 0;
        }

        return $result;
    }
}
