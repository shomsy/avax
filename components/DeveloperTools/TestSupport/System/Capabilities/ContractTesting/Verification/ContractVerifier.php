<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\TestSupport\System\Capabilities\ContractTesting\Verification;

use Avax\Components\DeveloperTools\TestSupport\System\Capabilities\ContractTesting\BreakingChangesReport;
use Avax\Components\DeveloperTools\TestSupport\System\Capabilities\ContractTesting\ComponentContractResult;
use Avax\Components\DeveloperTools\TestSupport\System\Capabilities\ContractTesting\ContractVerificationReport;

final class ContractVerifier
{
    /** @var array<string, array> */
    private array $contracts = [];

    public function registerContract(string $componentClass, array $contract) : void
    {
        $this->contracts[$componentClass] = $contract;
    }

    public function verify() : ContractVerificationReport
    {
        $results = [];

        foreach (array_keys($this->contracts) as $component) {
            $results[] = $this->verifyComponent($component);
        }

        return new ContractVerificationReport($results);
    }

    public function verifyComponent(string $componentClass) : ComponentContractResult
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

        if (! class_exists($componentClass) && ! interface_exists($componentClass)) {
            return new ComponentContractResult(
                component: $componentClass,
                passed   : false,
                checks   : [],
                error    : sprintf('Class or interface [%s] not found', $componentClass),
            );
        }

        $checks = [];
        $overallPassed = true;

        foreach ($contract['methods'] ?? [] as $method) {
            $methodName = $method['name'];
            $exists     = method_exists($componentClass, $methodName);

            if (! $exists) {
                $overallPassed = false;
            }

            $checks[] = [
                'method' => $methodName,
                'signature' => $method['signature'] ?? null,
                'passed' => $exists,
            ];
        }

        return new ComponentContractResult(
            component: $componentClass,
            passed   : $overallPassed,
            checks   : $checks,
        );
    }
}
