<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\InspectStaticState;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;

final readonly class InspectStaticState
{
    public function __construct(
        private RuntimeSafety $runtimeSafety = new RuntimeSafety(),
    ) {}

    /**
     * @return list<RuntimeSafetyFinding>
     */
    public function inspect() : array
    {
        return $this->runtimeSafety->staticScanner()->scan();
    }
}
