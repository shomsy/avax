<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Tenant;

enum TenantMemberState: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
}
