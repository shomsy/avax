<?php

declare(strict_types=1);

namespace Avax\Components\Framework\System\Flows\ResetApplicationState;

use Avax\Components\Framework\System\Capabilities\StateReset\StateResetRegistry;

final class ResetComponentState
{
    public function __construct(
        private readonly StateResetRegistry $registry,
    ) {
    }

    public function reset(): void
    {
        $this->registry->resetAll();
    }
}