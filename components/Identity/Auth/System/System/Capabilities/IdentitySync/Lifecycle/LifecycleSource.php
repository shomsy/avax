<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\Lifecycle;

enum LifecycleSource: string
{
    case ADMIN      = 'admin';
    case FEDERATION = 'federation';
    case SCIM       = 'scim';
}
