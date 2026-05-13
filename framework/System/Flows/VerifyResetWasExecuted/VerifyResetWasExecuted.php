<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\VerifyResetWasExecuted;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;

final class VerifyResetWasExecuted
{
    private RuntimeSafety $runtimeSafety;

    public function __construct(?RuntimeSafety $runtimeSafety = null)
    {
        $this->runtimeSafety = $runtimeSafety ?? RuntimeSafety::create();
    }

    /**
     * @return list<RuntimeSafetyFinding>
     */
    public function verify(): array
    {
        return $this->runtimeSafety->resetVerifier()->verify();
    }
}
