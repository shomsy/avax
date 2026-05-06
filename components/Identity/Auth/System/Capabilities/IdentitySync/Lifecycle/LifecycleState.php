<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle;

enum LifecycleState: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case DEPROVISIONED = 'deprovisioned';
}
