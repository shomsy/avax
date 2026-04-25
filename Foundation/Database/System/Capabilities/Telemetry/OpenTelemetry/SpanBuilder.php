<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Telemetry\OpenTelemetry;

final class SpanBuilder
{
    public function build(
        string      $query,
        array|null  $bindings = null,
        string|null $connection = null,
        int|null    $rows = null,
        string|null $error = null,
        float|null  $startTime = null,
        float|null  $endTime = null,
    ) : QuerySpan
    {
        $bindings ??= [];
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
