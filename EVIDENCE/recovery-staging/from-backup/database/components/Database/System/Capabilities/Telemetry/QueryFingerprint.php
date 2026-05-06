<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Telemetry;

final class QueryFingerprint
{
    public function hash(string $sql): string
    {
        return hash(algo: 'sha256', data: $this->normalize(sql: $sql));
    }

    public function normalize(string $sql): string
    {
        $sql = preg_replace(pattern: "/'(?:''|[^'])*'/", replacement: '?', subject: $sql) ?? $sql;
        $sql = preg_replace(pattern: '/\b\d+(?:\.\d+)?\b/', replacement: '?', subject: $sql) ?? $sql;
        $sql = preg_replace(pattern: '/\s+/', replacement: ' ', subject: trim(string: $sql)) ?? $sql;

        return strtolower(string: $sql);
    }
}
