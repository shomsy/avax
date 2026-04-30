<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends;

use Override;

final class PrometheusBackend implements MetricsBackend
{
    /** @var array<string, float> */
    private array $counters = [];

    /** @var array<string, float> */
    private array $gauges = [];

    /** @var array<string, array<float>> */
    private array $histograms = [];

    /** @var array<string, array<int>> */
    private array $timings = [];

    #[Override]
    public function increment(string $metric, int $value = 1) : void
    {
        if (! isset($this->counters[$metric])) {
            $this->counters[$metric] = 0;
        }

        $this->counters[$metric] += $value;
    }

    #[Override]
    public function gauge(string $metric, float $value) : void
    {
        $this->gauges[$metric] = $value;
    }

    #[Override]
    public function histogram(string $metric, float $value) : void
    {
        if (! isset($this->histograms[$metric])) {
            $this->histograms[$metric] = [];
        }

        $this->histograms[$metric][] = $value;
    }

    #[Override]
    public function timing(string $metric, int $milliseconds) : void
    {
        if (! isset($this->timings[$metric])) {
            $this->timings[$metric] = [];
        }

        $this->timings[$metric][] = $milliseconds;
    }

    #[Override]
    public function flush() : void {}

    public function getCounters() : array
    {
        return $this->counters;
    }

    public function getGauges() : array
    {
        return $this->gauges;
    }

    public function getHistograms() : array
    {
        return $this->histograms;
    }

    public function getTimings() : array
    {
        return $this->timings;
    }

    public function render() : string
    {
        $output = [];

        foreach ($this->counters as $metric => $value) {
            $output[] = sprintf('# TYPE %s counter', $metric);
            $output[] = sprintf('%s %s', $metric, $value);
        }

        foreach ($this->gauges as $metric => $value) {
            $output[] = sprintf('# TYPE %s gauge', $metric);
            $output[] = sprintf('%s %s', $metric, $value);
        }

        return implode("\n", $output) . "\n";
    }
}
