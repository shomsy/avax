<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface;

final readonly class CheckResult
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string  $status,
        public float   $latencyMs = 0.0,
        public string|null $error = null,
        public array   $meta = [],
    ) {}

    public function withLatency(float $latencyMs) : self
    {
        return new self(
            status   : $this->status,
            latencyMs: $latencyMs,
            error    : $this->error,
            meta     : $this->meta,
        );
    }

    public function toArray() : array
    {
        return [
            'status'     => $this->status,
            'latency_ms' => $this->latencyMs,
            'error'      => $this->error,
            'meta'       => $this->meta,
        ];
    }
}
