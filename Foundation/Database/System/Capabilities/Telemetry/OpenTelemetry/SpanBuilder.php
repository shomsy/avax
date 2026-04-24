<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Telemetry\OpenTelemetry;

final class SpanBuilder
{
    public function build(
        string  $query,
        array   $bindings = [],
        ?string $connection = null,
        ?int    $rows = null,
        ?string $error = null,
        ?float  $startTime = null,
        ?float  $endTime = null,
    ) : QuerySpan
    {
        $startTime ??= microtime(as_float: true);
        $endTime   ??= microtime(as_float: true);

        return new QuerySpan(
            query     : $query,
            bindings  : $bindings,
            startTime : $startTime,
            endTime   : $endTime,
            connection: $connection,
            rows      : $rows,
            error     : $error,
        );
    }
}
