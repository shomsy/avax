<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\TestSupport\System\PublicSurface;

use Avax\Components\DeveloperTools\TestSupport\System\Capabilities\ContractTesting\Verification\ContractVerifier;

final class Testing
{
    /**
     * @param array<string, mixed> $contracts
     *
     * @return array{verified: int, results: array<string, mixed>}
     */
    public function verifyContracts(array $contracts): array
    {
        $verifier = new ContractVerifier();

        foreach ($contracts as $component => $contract) {
            $verifier->registerContract($component, $contract);
        }

        $report = $verifier->verify();

        return [
            'verified' => count($report->results),
            'results' => $report->toArray(),
        ];
    }
}
