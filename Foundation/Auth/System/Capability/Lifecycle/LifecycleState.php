<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

/**
 * Lifecycle states for identity management.
 */
enum LifecycleState: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case DISABLED = 'disabled';
    case DEPROVISIONED = 'deprovisioned';
    case PENDING = 'pending';
}

/**
 * Lifecycle event types.
 */
enum LifecycleEvent: string
{
    case JOIN = 'join';
    case MOVE = 'move';
    case LEAVE = 'leave';
    case SUSPEND = 'suspend';
    case REINSTATE = 'reinstate';
    case DISABLE = 'disable';
    case ENABLE = 'enable';
    case DEPROVISION = 'deprovision';
}