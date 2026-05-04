<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Tracing;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\SpanId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId;
use Throwable;

class Span
{
    private readonly float $startTime;

    private ?float $endTime = null;

    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    public function __construct(
        public readonly string   $name,
        public readonly string   $operation,
        public readonly ?TraceId $traceId = null,
        public readonly ?SpanId  $parentSpanId = null,
    )
    {
        $this->startTime = hrtime(true) / 1e9;
    }

    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function recordException(Throwable $throwable): self
    {
        return $this;
    }

    public function end(): float
    {
        if ($this->endTime === null) {
            $this->endTime = hrtime(true) / 1e9;
        }

        return $this->endTime - $this->startTime;
    }

    public function duration(): ?float
    {
        return $this->endTime ? $this->endTime - $this->startTime : null;
    }
}
