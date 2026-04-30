<?php

declare(strict_types=1);

namespace Avax\Components\QueryGovernance\System\PublicSurface;

use Avax\Components\QueryGovernance\System\Capabilities\Detection\NPlusOneDetector;
use Avax\Components\QueryGovernance\System\Capabilities\Detection\SlowQueryDetector;

final class QueryGovernance
{
    private static bool $enabled = false;

    public static function enable() : void
    {
        self::$enabled = true;
    }

    public static function disable() : void
    {
        self::$enabled = false;
    }

    public static function isEnabled() : bool
    {
        return self::$enabled;
    }

    public static function detectNPlusOne(string $query, array $bindings) : bool
    {
        return NPlusOneDetector::detect($query, $bindings);
    }

    public static function checkSlowQuery(float $durationMs, float $thresholdMs = 100.0) : bool
    {
        return SlowQueryDetector::isSlow($durationMs, $thresholdMs);
    }

    public static function enforceBindings(string $query) : bool
    {
        return ! preg_match('/["\']\s*\.\s*\$/', $query);
    }
}

final class QueryReport
{
    /** @var array<string, int> */
    public array $nPlusOne = [];
    /** @var list<array{query: string, duration: float}> */
    public array $slowQueries = [];
    /** @var list<string> */
    public array $bindingViolations = [];

    public function addNPlusOne(string $location, int $count) : void
    {
        $this->nPlusOne[$location] = $count;
    }

    public function addSlowQuery(string $query, float $duration) : void
    {
        $this->slowQueries[] = ['query' => $query, 'duration' => $duration];
    }

    public function addBindingViolation(string $location) : void
    {
        $this->bindingViolations[] = $location;
    }

    /**
     * @return array{n_plus_one: array<string, int>, slow_queries: list<array{query: string, duration: float}>,
     *                           binding_violations: list<string>}
     */
    public function toArray() : array
    {
        return [
            'n_plus_one'         => $this->nPlusOne,
            'slow_queries'       => $this->slowQueries,
            'binding_violations' => $this->bindingViolations,
        ];
    }
}