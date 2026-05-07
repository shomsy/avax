<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Flows\RunSmokeChecks;

final readonly class RunSmokeChecks
{
    /**
     * @param list<callable(): bool> $checks
     *
     * @return array{passed: bool, results: list<array{check: string, passed: bool}>}
     */
    public function run(array $checks) : array
    {
        $results   = [];
        $allPassed = true;

        foreach ($checks as $i => $check) {
            $passed    = $check();
            $results[] = [
                'check'  => "check_{$i}",
                'passed' => $passed,
            ];

            if (! $passed) {
                $allPassed = false;
            }
        }

        return [
            'passed'  => $allPassed,
            'results' => $results,
        ];
    }
}
