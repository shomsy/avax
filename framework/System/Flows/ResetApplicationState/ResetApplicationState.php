<?php

declare(strict_types=1);

namespace Avax\Components\Framework\System\Flows\ResetApplicationState;

use Avax\Components\Framework\System\Capabilities\StateReset\StateResetRegistry;

final class ResetApplicationState
{
    public function __construct(
        private readonly StateResetRegistry $registry,
    ) {
    }

    public function reset(): StateResetReport
    {
        $this->resetRequestScope();
        $this->resetRuntimeContext();
        $this->resetDiagnosticsContext();
        $this->resetComponentState();

        return new StateResetReport(
            success: true,
            componentsReset: $this->registry->getRegisteredCount(),
        );
    }

    private function resetRequestScope(): void
    {
    }

    private function resetRuntimeContext(): void
    {
    }

    private function resetDiagnosticsContext(): void
    {
    }

    private function resetComponentState(): void
    {
        $this->registry->resetAll();
    }
}