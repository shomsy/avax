<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Flows\VerifyRelease;

final readonly class VerifyRelease
{
    /**
     * @param list<callable(): bool> $checks
     *
     * @return array{ready: bool, checks: list<array{name: string, passed: bool}>}
     */
    public function verify(array $checks) : array
    {
        $results   = [];
        $allPassed = true;

        foreach ($checks as $i => $check) {
            $passed    = $check();
            $results[] = [
                'name'   => "check_{$i}",
                'passed' => $passed,
            ];

            if (! $passed) {
                $allPassed = false;
            }
        }

        return [
            'ready'  => $allPassed,
            'checks' => $results,
        ];
    }
}
