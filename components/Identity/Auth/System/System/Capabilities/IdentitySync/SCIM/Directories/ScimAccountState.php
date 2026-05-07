<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories;

enum ScimAccountState: string
{
    case ACTIVE    = 'active';
    case SUSPENDED = 'suspended';
    case DISABLED  = 'disabled';
}
