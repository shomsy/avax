<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\EndpointPosture;

/**
 * Policy thresholds for endpoint posture decisions.
 */
final readonly class EndpointPosturePolicy
{
    public float $stepUpThreshold;

    public float $denyThreshold;

    public function __construct(
        ?float       $denyThreshold = null,
        ?float       $stepUpThreshold = null,
        public float $quarantineThreshold = 0.3,
    )
    {
        $denyThreshold         ??= 0.8;
        $stepUpThreshold       ??= 0.5;
        $this->denyThreshold   = $denyThreshold;
        $this->stepUpThreshold = $stepUpThreshold;
    }
}
