<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends;

final class StatsDBackend implements MetricsBackend
{
    /** @var array<string, int> */
    private array $counters = [];

    /** @var array<string, float> */
    private array $gauges = [];

    /** @var array<string, array<float>> */
    private array $histograms = [];

    /** @var array<string, array<int>> */
    private array $timings = [];

    /** @var array<string> */
    private array $messages = [];

    public function increment(string $metric, int $value = 1) : void
    {
        $key                     = "{$metric}:{$value}|c";
        $this->messages[]        = $key;
        $this->counters[$metric] = ($this->counters[$metric] ?? 0) + $value;
    }

    public function gauge(string $metric, float $value) : void
    {
        $key                   = "{$metric}:{$value}|g";
        $this->messages[]      = $key;
        $this->gauges[$metric] = $value;
    }

    public function histogram(string $metric, float $value) : void
    {
        $key              = "{$metric}:{$value}|h";
        $this->messages[] = $key;

        if (! isset($this->histograms[$metric])) {
            $this->histograms[$metric] = [];
        }

        $this->histograms[$metric][] = $value;
    }

    public function timing(string $metric, int $milliseconds) : void
    {
        $key              = "{$metric}:{$milliseconds}|ms";
        $this->messages[] = $key;

        if (! isset($this->timings[$metric])) {
            $this->timings[$metric] = [];
        }

        $this->timings[$metric][] = $milliseconds;
    }

    public function flush() : void
    {
        $this->messages = [];
    }

    public function getMessages() : array
    {
        return $this->messages;
    }

    public function getCounters() : array
    {
        return $this->counters;
    }

    public function getGauges() : array
    {
        return $this->gauges;
    }

    public function getTimings() : array
    {
        return $this->timings;
    }

    public function getPercentile(float $percentile) : array
    {
        $result = [];

        foreach ($this->timings as $metric => $values) {
            sort($values);
            $index           = (int) ceil(($percentile / 100) * count($values)) - 1;
            $result[$metric] = $values[max(0, $index)] ?? 0;
        }

        return $result;
    }
}