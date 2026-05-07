<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RiskBasedAccess\EndpointPosture;

/**
 * Endpoint posture decision outcomes.
 */
enum EndpointPostureDecision: string
{
    case ALLOW      = 'allow';
    case STEP_UP    = 'step_up';
    case DENY       = 'deny';
    case QUARANTINE = 'quarantine';
}
