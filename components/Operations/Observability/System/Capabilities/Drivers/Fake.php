<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Drivers;

use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Counter;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Gauge;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\Span;

class Fake implements ObservabilityAdapterInterface
{
    /**
     * @var array<int, Span>
     */
    private array $spans = [];

    /**
     * @var array<string, Counter>
     */
    private array $counters = [];

    /**
     * @var array<string, Gauge>
     */
    private array $gauges = [];

    /**
     * @var array<int, StructuredLogRecord>
     */
    private array $logs = [];

    public function recordSpan(Span $span): void
    {
        $this->spans[] = $span;
    }

    public function incrementCounter(string $name, float $value = 1.0): void
    {
        if (! isset($this->counters[$name])) {
            $this->counters[$name] = new Counter($name);
        }

        $this->counters[$name]->increment($value);
    }

    public function setGauge(string $name, float $value): void
    {
        if (! isset($this->gauges[$name])) {
            $this->gauges[$name] = new Gauge($name);
        }

        $this->gauges[$name]->set($value);
    }

    public function writeLog(StructuredLogRecord $structuredLogRecord): void
    {
        $this->logs[] = $structuredLogRecord;
    }

    public function getSpans(): array
    {
        return $this->spans;
    }

    public function getCounter(string $name): ?Counter
    {
        return $this->counters[$name] ?? null;
    }

    public function getGauge(string $name): ?Gauge
    {
        return $this->gauges[$name] ?? null;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function reset(): void
    {
        $this->spans = [];
        $this->logs = [];
    }
}
