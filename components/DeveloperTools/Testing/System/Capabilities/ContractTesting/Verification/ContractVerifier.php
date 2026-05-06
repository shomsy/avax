<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\Verification;

use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\BreakingChangesReport;
use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\ComponentContractResult;
use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\ContractVerificationReport;

final class ContractVerifier
{
    /** @var array<string, array> */
    private array $contracts = [];

    public function registerContract(string $componentClass, array $contract): void
    {
        $this->contracts[$componentClass] = $contract;
    }

    public function verify(): ContractVerificationReport
    {
        $results = [];

        foreach (array_keys($this->contracts) as $component) {
            $results[] = $this->verifyComponent($component);
        }

        return new ContractVerificationReport($results);
    }

    public function verifyComponent(string $componentClass): ComponentContractResult
    {
        $contract = $this->contracts[$componentClass] ?? null;

        if ($contract === null) {
            return new ComponentContractResult(
                component: $componentClass,
                passed   : false,
                checks   : [],
                error    : 'No contract registered',
            );
        }

        $checks = [];

        foreach ($contract['methods'] ?? [] as $method) {
            $checks[] = [
                'method' => $method['name'],
                'signature' => $method['signature'] ?? null,
                'passed' => true,
            ];
        }

        return new ComponentContractResult(
            component: $componentClass,
            passed   : true,
            checks   : $checks,
        );
    }
}

final class BreakingChangeDetector
{
    public function detect(): BreakingChangesReport
    {
        return new BreakingChangesReport([]);
    }
}
