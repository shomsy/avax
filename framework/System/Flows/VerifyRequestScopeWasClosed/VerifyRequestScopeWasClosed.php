<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\VerifyRequestScopeWasClosed;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;

final readonly class VerifyRequestScopeWasClosed
{
    public function __construct(
        private RuntimeSafety $runtimeSafety = new RuntimeSafety,
    ) {}

    /**
     * @return list<RuntimeSafetyFinding>
     */
    public function verify() : array
    {
        return $this->runtimeSafety->resetVerifier()->verify();
    }
}
