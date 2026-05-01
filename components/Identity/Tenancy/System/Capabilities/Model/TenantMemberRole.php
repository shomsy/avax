<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Model;

enum TenantMemberRole: string
{
    case OWNER  = 'owner';
    case ADMIN  = 'admin';
    case MEMBER = 'member';
    case SUPPORT = 'support';
}
