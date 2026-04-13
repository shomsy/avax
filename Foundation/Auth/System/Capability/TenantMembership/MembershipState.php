<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantMembership;

/**
 * Represents the state/status of a membership within a tenant.
 */
enum MembershipState: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';
    case INVITED = 'invited';
}