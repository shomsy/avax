<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Drivers;

use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\Span;

interface ObservabilityAdapterInterface
{
    public function recordSpan(Span $span): void;

    public function incrementCounter(string $name, float $value = 1.0): void;

    public function setGauge(string $name, float $value): void;

    public function writeLog(StructuredLogRecord $structuredLogRecord): void;

    public function reset(): void;
}
