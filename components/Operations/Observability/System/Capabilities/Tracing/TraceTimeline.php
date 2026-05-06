<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Tracing;

class TraceTimeline
{
    /**
     * @var array<int, Span>
     */
    private array $spans = [];

    public function add(Span $span): void
    {
        $this->spans[] = $span;
    }

    public function toArray(): array
    {
        return array_map(
            fn (Span $span): array => [
                'name' => $span->name,
                'operation' => $span->operation,
                'duration' => $span->duration(),
            ],
            $this->spans
        );
    }
}
