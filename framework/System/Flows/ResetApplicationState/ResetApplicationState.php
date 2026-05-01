<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ResetApplicationState;

use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;

final class ResetApplicationState
{
    public function __construct(
        private StateResetRegistry|null $registry = null,
    ) {}

    public function reset() : StateResetReport
    {
        $this->resetRequestScope();
        $this->resetRuntimeContext();
        $this->resetDiagnosticsContext();

        $report = $this->registry !== null
            ? $this->registry->resetAll()
            : new StateResetReport(resetComponents: [], failures: []);

        return $report;
    }

    private function resetRequestScope() : void
    {
    }

    private function resetRuntimeContext() : void
    {
    }

    private function resetDiagnosticsContext() : void
    {
    }
}