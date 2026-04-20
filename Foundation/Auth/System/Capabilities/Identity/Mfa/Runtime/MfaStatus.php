<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa;

/**
 * Stable MFA enrollment state for a user.
 */
enum MfaStatus: string
{
    case DISABLED           = 'disabled';
    case ENROLLMENT_PENDING = 'enrollment_pending';
    case ENABLED            = 'enabled';
}
