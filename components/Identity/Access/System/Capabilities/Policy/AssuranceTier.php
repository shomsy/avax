<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy;

/**
 * Assurance level attached to an identity policy.
 */
enum AssuranceTier: string
{
    case STANDARD = 'standard';
    case HIGH = 'high';
    case PHISHING_RESISTANT = 'phishing_resistant';
    case EMERGENCY = 'emergency';
}
