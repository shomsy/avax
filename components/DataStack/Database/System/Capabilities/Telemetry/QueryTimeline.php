<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\OpenTelemetry\QuerySpan;

final class QueryTimeline
{
    /** @var list<QuerySpan> */
    private array $spans = [];

    public function add(QuerySpan $querySpan): self
    {
        $this->spans[] = $querySpan;

        return $this;
    }

    public function all(): array
    {
        return $this->spans;
    }

    public function totalDurationMs(): float
    {
        return array_sum(array: array_map(callback: static fn (QuerySpan $querySpan): float => $querySpan->getDurationMs(), array: $this->spans));
    }

    public function reset(): void
    {
        $this->spans = [];
    }
}
