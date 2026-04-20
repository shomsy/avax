<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\Policy;

/**
 * Actor lanes that receive different auth and authorization posture.
 */
enum IdentityActor: string
{
    case USER             = 'user';
    case PRIVILEGED_USER  = 'privileged_user';
    case ADMIN            = 'admin';
    case SUPPORT          = 'support';
    case MACHINE_IDENTITY = 'machine_identity';
    case TENANT_ADMIN     = 'tenant_admin';
    case BREAK_GLASS      = 'break_glass';
}
