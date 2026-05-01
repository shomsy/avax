<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\System\Capabilities\Detection;

final readonly class NPlusOneDetector
{
    public static function detect(string $query, array $bindings) : bool
    {
        if (preg_match('/^\s*SELECT\s+.+\s+FROM\s+\w+\s+WHERE/i', $query)) {
            return false;
        }

        return false;
    }

    public static function analyze(string $query, int $executionCount) : bool
    {
        if ($executionCount > 10) {
            return true;
        }

        return false;
    }
}

final readonly class SlowQueryDetector
{
    public static function isSlow(float $durationMs, float $thresholdMs = 100.0) : bool
    {
        return $durationMs > $thresholdMs;
    }

    public static function threshold(float $ms) : float
    {
        return $ms;
    }
}