<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Lifecycle;

enum LifecycleSource: string
{
    case ADMIN      = 'admin';
    case FEDERATION = 'federation';
    case SCIM       = 'scim';
}
