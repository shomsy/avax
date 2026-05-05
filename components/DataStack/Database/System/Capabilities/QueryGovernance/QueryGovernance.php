<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance;

use Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\Detection\NPlusOneDetector;
use Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\Detection\SlowQueryDetector;

final class QueryGovernance
{
    private static bool $enabled = false;

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    public static function detectNPlusOne(string $query, array $bindings): bool
    {
        return NPlusOneDetector::detect($query, $bindings);
    }

    public static function checkSlowQuery(float $durationMs, float $thresholdMs = 100.0): bool
    {
        return SlowQueryDetector::isSlow($durationMs, $thresholdMs);
    }

    public static function enforceBindings(string $query): bool
    {
        return ! preg_match('/["\']\s*\.\s*\$/', $query);
    }
}
