<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\Detection;

final readonly class NPlusOneDetector
{
    public static function detect(string $query) : bool
    {
        if (preg_match('/^\s*SELECT\s+.+\s+FROM\s+\w+\s+WHERE/i', $query)) {
            return false;
        }

        return false;
    }

    public static function analyze(string $query, int $executionCount): bool
    {
        return $executionCount > 10;
    }
}
