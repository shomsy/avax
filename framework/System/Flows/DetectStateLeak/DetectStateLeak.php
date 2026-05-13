<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\DetectStateLeak;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;

final class DetectStateLeak
{
    private RuntimeSafety $runtimeSafety;

    public function __construct(?RuntimeSafety $runtimeSafety = null)
    {
        $this->runtimeSafety = $runtimeSafety ?? RuntimeSafety::create();
    }

    /**
     * @return list<RuntimeSafetyFinding>
     */
    public function detect(): array
    {
        return $this->runtimeSafety->leakDetector()->detect();
    }
}
