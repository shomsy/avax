<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Model;

enum TenantMemberRole: string
{
    case OWNER   = 'owner';
    case ADMIN   = 'admin';
    case MEMBER  = 'member';
    case SUPPORT = 'support';
}
