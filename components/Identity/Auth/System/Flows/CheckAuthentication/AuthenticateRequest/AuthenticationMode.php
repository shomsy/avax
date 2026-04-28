<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

/**
 * Transport that established the current authentication context.
 */
enum AuthenticationMode: string
{
    case NONE    = 'none';
    case SESSION = 'session';
    case TOKEN   = 'token';
    case HYBRID  = 'hybrid';
}
