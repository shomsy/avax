<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Diagnostics;

final class Diagnostics
{
    private static ?CorrelationId $correlationId = null;

    private static ?TraceId $traceId = null;

    public static function correlationId(): CorrelationId
    {
        return self::$correlationId ??= CorrelationId::generate();
    }

    public static function traceId(): TraceId
    {
        return self::$traceId ??= TraceId::generate();
    }

    public static function setCorrelationId(CorrelationId $correlationId): void
    {
        self::$correlationId = $correlationId;
    }

    public static function setTraceId(TraceId $traceId): void
    {
        self::$traceId = $traceId;
    }

    public static function clear(): void
    {
        self::$correlationId = null;
        self::$traceId = null;
    }
}
