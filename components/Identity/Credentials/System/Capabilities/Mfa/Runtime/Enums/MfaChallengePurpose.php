<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums;

/**
 * Why the package requested MFA proof.
 */
enum MfaChallengePurpose: string
{
    case LOGIN = 'login';
    case STEP_UP = 'step_up';
}
