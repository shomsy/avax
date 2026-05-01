<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\OpenTelemetry;

final readonly class QuerySpan
{
    public function __construct(
        public string  $query,
        public array   $bindings = [],
        public float   $startTime = 0.0,
        public float   $endTime = 0.0,
        public ?string $connection = null,
        public ?int    $rows = null,
        public ?string $error = null,
    ) {}

    public function isSlow(int $thresholdMs = 1000): bool
    {
        return $this->getDurationMs() > $thresholdMs;
    }

    public function getDurationMs(): float
    {
        return ($this->endTime - $this->startTime) * 1000;
    }

    public function toArray(): array
    {
        return [
            'query'      => $this->query,
            'bindings'   => $this->bindings,
            'duration_ms' => $this->getDurationMs(),
            'connection' => $this->connection,
            'rows'       => $this->rows,
            'error'      => $this->error,
            'fingerprint' => $this->getFingerprint(),
        ];
    }

    public function getFingerprint(): string
    {
        $normalized = preg_replace(pattern: '/\?/', replacement: ':param', subject: $this->query);

        return md5(string: (string) $normalized);
    }
}
