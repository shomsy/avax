<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Telemetry\OpenTelemetry;

final class QuerySpan
{
    public function __construct(
        public readonly string  $query,
        public readonly array   $bindings = [],
        public readonly float   $startTime = 0.0,
        public readonly float   $endTime = 0.0,
        public readonly ?string $connection = null,
        public readonly ?int    $rows = null,
        public readonly ?string $error = null
    ) {}

    public function isSlow(int $thresholdMs = 1000) : bool
    {
        return $this->getDurationMs() > $thresholdMs;
    }

    public function getDurationMs() : float
    {
        return ($this->endTime - $this->startTime) * 1000;
    }

    public function toArray() : array
    {
        return [
            'query'       => $this->query,
            'bindings'    => $this->bindings,
            'duration_ms' => $this->getDurationMs(),
            'connection'  => $this->connection,
            'rows'        => $this->rows,
            'error'       => $this->error,
            'fingerprint' => $this->getFingerprint(),
        ];
    }

    public function getFingerprint() : string
    {
        $normalized = preg_replace(pattern: '/\?/', replacement: ':param', subject: $this->query);

        return md5(string: $normalized);
    }
}
