<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Model;

enum TenantMemberState: string
{
    case ACTIVE    = 'active';
    case SUSPENDED = 'suspended';
}
