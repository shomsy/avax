<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models;

/**
 * Stable reasons exposed by MFA challenge failures.
 */
enum MfaChallengeFailure: string
{
    case INVALID = 'invalid';
    case EXPIRED = 'expired';
    case LOCKED = 'locked';
    case NOT_FOUND = 'not_found';
    case NOT_ENABLED = 'not_enabled';
}
