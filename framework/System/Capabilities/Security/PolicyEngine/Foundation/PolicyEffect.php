<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation;

enum PolicyEffect: string
{
    case Allow = 'allow';
    case Deny = 'deny';
}
