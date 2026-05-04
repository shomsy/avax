<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\Verification;

use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\BreakingChangesReport;
use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\ComponentContractResult;
use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\ContractVerificationReport;

final class BreakingChangeDetector
{
    public function detect(): BreakingChangesReport
    {
        return new BreakingChangesReport([]);
    }
}
