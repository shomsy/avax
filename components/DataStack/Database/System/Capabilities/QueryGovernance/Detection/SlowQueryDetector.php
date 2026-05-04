<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\Detection;

final readonly class SlowQueryDetector
{
    public static function isSlow(float $durationMs, float $thresholdMs = 100.0): bool
    {
        return $durationMs > $thresholdMs;
    }

    public static function threshold(float $ms): float
    {
        return $ms;
    }
}
