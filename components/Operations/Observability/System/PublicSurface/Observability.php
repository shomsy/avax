<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\PublicSurface;

use Avax\Components\Operations\Observability\System\Capabilities\Audit\AuditEvent;
use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Counter;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Gauge;
use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Histogram;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\Span;

final readonly class Observability
{
    public static function trace(string $name, string $operation): Span
    {
        return new Span($name, $operation);
    }

    public static function counter(string $name): Counter
    {
        return new Counter($name);
    }

    public static function gauge(string $name): Gauge
    {
        return new Gauge($name);
    }

    public static function histogram(string $name): Histogram
    {
        return new Histogram($name);
    }

    public static function log(
        string $level,
        string $message,
        array  $context = [],
    ): StructuredLogRecord
    {
        return new StructuredLogRecord($level, $message, $context);
    }

    public static function audit(
        string $actor,
        string $action,
        string $target,
        array  $metadata = [],
    ): AuditEvent
    {
        return new AuditEvent($actor, $action, $target, $metadata);
    }
}