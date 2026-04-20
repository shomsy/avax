<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Lifecycle;

enum LifecycleState: string
{
    case ACTIVE        = 'active';
    case SUSPENDED     = 'suspended';
    case DEPROVISIONED = 'deprovisioned';
}
