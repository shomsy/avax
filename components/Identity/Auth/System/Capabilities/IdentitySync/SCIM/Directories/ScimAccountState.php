<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support;

enum ScimAccountState: string
{
    case ACTIVE   = 'active';
    case SUSPENDED = 'suspended';
    case DISABLED = 'disabled';
}
