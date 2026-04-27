<?php

declare(strict_types=1);

namespace Avax\Components\Framework\System\Capabilities\Diagnostics;

final class Diagnostics
{
    private static CorrelationId|null $correlationId = null;
    private static TraceId|null $traceId = null;

    public static function correlationId(): CorrelationId
    {
        return self::$correlationId ??= CorrelationId::generate();
    }

    public static function traceId(): TraceId
    {
        return self::$traceId ??= TraceId::generate();
    }

    public static function setCorrelationId(CorrelationId $id): void
    {
        self::$correlationId = $id;
    }

    public static function setTraceId(TraceId $id): void
    {
        self::$traceId = $id;
    }

    public static function clear(): void
    {
        self::$correlationId = null;
        self::$traceId = null;
    }
}