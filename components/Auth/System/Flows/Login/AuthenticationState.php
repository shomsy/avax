<?php

declare(strict_types=1);

namespace components\Auth\System\Flows\Login;

/**
 * Stable public state of an authentication attempt.
 */
enum AuthenticationState: string
{
    case AUTHENTICATED = 'authenticated';
    case MFA_REQUIRED  = 'mfa_required';
}
