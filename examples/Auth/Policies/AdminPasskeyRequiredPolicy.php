<?php

declare(strict_types=1);

namespace Avax\Examples\Auth\Policies;

use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\Policy\IdentityPolicyCatalog;

final readonly class AdminPasskeyRequiredPolicy
{
    public function execute(): AccessPolicy
    {
        return AccessPolicy::forIdentityPolicy(identityPolicy: IdentityPolicyCatalog::admin());
    }
}
