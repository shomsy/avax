<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

enum LifecycleSource: string
{
    case ADMIN = 'admin';
    case FEDERATION = 'federation';
    case SCIM = 'scim';
}
