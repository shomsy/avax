<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Telemetry;

use Avax\Database\System\Capabilities\Telemetry\OpenTelemetry\QuerySpan;

final class SlowQueryDetector
{
    /** @var list<QuerySpan> */
    private array $slowQueries = [];

    public function __construct(private readonly int $thresholdMs = 1000) {}

    public function record(QuerySpan $span) : bool
    {
        if (! $span->isSlow(thresholdMs: $this->thresholdMs)) {
            return false;
        }

        $this->slowQueries[] = $span;

        return true;
    }

    public function all() : array
    {
        return $this->slowQueries;
    }

    public function reset() : void
    {
        $this->slowQueries = [];
    }
}
