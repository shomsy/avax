<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector;

use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Counter;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Gauge;

/**
 * In-memory metrics collector for testing and lightweight runtimes.
 *
 * Provides a simple registry for counters, gauges, and histograms
 * with snapshot capability for assertions.
 */
final class MetricsCollector
{
    /** @var array<string, Counter> */
    private array $counters = [];

    /** @var array<string, Gauge> */
    private array $gauges = [];

    /** @var array<string, ExtendedHistogram> */
    private array $histograms = [];

    /**
     * Increment a named counter.
     */
    public function incrementCounter(string $name, float $amount = 1.0) : void
    {
        if (!isset($this->counters[$name])) {
            $this->counters[$name] = new Counter(name: $name);
        }

        $this->counters[$name]->increment($amount);
    }

    /**
     * Get counter value.
     */
    public function getCounter(string $name) : float
    {
        return isset($this->counters[$name]) ? $this->counters[$name]->getValue() : 0.0;
    }

    /**
     * Set a gauge value.
     */
    public function setGauge(string $name, float $value) : void
    {
        if (!isset($this->gauges[$name])) {
            $this->gauges[$name] = new Gauge(name: $name);
        }

        $this->gauges[$name]->set($value);
    }

    /**
     * Get gauge value.
     */
    public function getGauge(string $name) : float
    {
        return isset($this->gauges[$name]) ? $this->gauges[$name]->getValue() : 0.0;
    }

    /**
     * Record a histogram observation.
     */
    public function observeHistogram(string $name, float $value) : void
    {
        if (!isset($this->histograms[$name])) {
            $this->histograms[$name] = new ExtendedHistogram(name: $name, unit: 'ms');
        }

        $this->histograms[$name]->observe($value);
    }

    /**
     * Get histogram stats.
     *
     * @return array{count: int, sum: float, min: float|null, max: float|null}
     */
    public function getHistogramStats(string $name) : array
    {
        $histogram = $this->histograms[$name] ?? null;

        if ($histogram === null) {
            return ['count' => 0, 'sum' => 0.0, 'min' => null, 'max' => null];
        }

        return $histogram->stats();
    }

    /**
     * Snapshot of all metrics.
     *
     * @return array{counters: array<string, float>, gauges: array<string, float>, histograms: array<string, array{count: int, sum: float, min: float|null, max: float|null}>}
     */
    public function snapshot() : array
    {
        $counters = [];
        foreach ($this->counters as $name => $counter) {
            $counters[$name] = $counter->getValue();
        }

        $gauges = [];
        foreach ($this->gauges as $name => $gauge) {
            $gauges[$name] = $gauge->getValue();
        }

        $histograms = [];
        foreach ($this->histograms as $name => $histogram) {
            $histograms[$name] = $histogram->stats();
        }

        return [
            'counters' => $counters,
            'gauges' => $gauges,
            'histograms' => $histograms,
        ];
    }

    public function clear() : void
    {
        $this->counters = [];
        $this->gauges = [];
        $this->histograms = [];
    }
}
