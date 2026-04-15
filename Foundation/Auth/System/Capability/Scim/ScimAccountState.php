<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Scim;

enum ScimAccountState: string
{
    case ACTIVE    = 'active';
    case SUSPENDED = 'suspended';
    case DISABLED  = 'disabled';
}
