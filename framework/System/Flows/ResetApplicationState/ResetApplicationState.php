<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ResetApplicationState;

use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;

final readonly class ResetApplicationState
{
    public function __construct(
        private ?StateResetRegistry $stateResetRegistry = null,
    ) {}

    public function reset() : StateResetReport
    {
        $this->resetRequestScope();
        $this->resetRuntimeContext();
        $this->resetDiagnosticsContext();

        return $this->stateResetRegistry instanceof StateResetRegistry
            ? $this->stateResetRegistry->resetAll()
            : new StateResetReport(resetComponents: [], failures: []);
    }

    private function resetRequestScope() : void {}

    private function resetRuntimeContext() : void {}

    private function resetDiagnosticsContext() : void {}
}
