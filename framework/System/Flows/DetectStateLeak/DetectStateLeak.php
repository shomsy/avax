<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\DetectStateLeak;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;

final readonly class DetectStateLeak
{
    public function __construct(
        private RuntimeSafety $runtimeSafety = new RuntimeSafety,
    ) {}

    /**
     * @return list<RuntimeSafetyFinding>
     */
    public function detect() : array
    {
        return $this->runtimeSafety->leakDetector()->detect();
    }
}
