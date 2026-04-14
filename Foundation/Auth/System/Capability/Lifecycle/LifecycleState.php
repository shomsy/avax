<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

enum LifecycleState: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case DEPROVISIONED = 'deprovisioned';
}
