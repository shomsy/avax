<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Tenancy\Model;

enum TenantMemberState: string
{
    case ACTIVE    = 'active';
    case SUSPENDED = 'suspended';
}
