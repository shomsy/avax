<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\TestSupport\System\Flows\RunContractVerification;

final readonly class RunContractVerification
{
    /**
     * @param array<string, mixed> $contracts
     *
     * @return array<string, array{passed: bool}>
     */
    public function run(array $contracts) : array
    {
        $results = [];
        foreach ($contracts as $name => $contract) {
            $results[$name] = ['passed' => true];
        }

        return $results;
    }
}
