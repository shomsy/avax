<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\EndpointPosture;

/**
 * Endpoint posture signal data.
 */
final readonly class EndpointPostureSignalData
{
    public function __construct(public EndpointPostureSignal $type, public float $score, public bool $anomalous, public string $detail)
    {
    }
}
