<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime;

/**
 * Why the package requested MFA proof.
 */
enum MfaChallengePurpose: string
{
    case LOGIN   = 'login';
    case STEP_UP = 'step_up';
}
