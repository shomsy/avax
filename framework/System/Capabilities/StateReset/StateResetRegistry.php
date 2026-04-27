<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\StateReset;

use Throwable;

final class StateResetRegistry
{
    /**
     * @var array<string, ResettableState>
     */
    private array $states = [];

    public function register(string $name, ResettableState $state): void
    {
        $this->states[$name] = $state;
    }

    public function resetAll(): StateResetReport
    {
        $resetComponents = [];
        $failures        = [];

        foreach ($this->states as $name => $state) {
            try {
                $state->resetState();
                $resetComponents[] = $name;
            } catch (Throwable $throwable) {
                $failures[$name] = $throwable;
            }
        }

        return new StateResetReport(
            resetComponents: $resetComponents,
            failures       : $failures,
        );
    }
}
