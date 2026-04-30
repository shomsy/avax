<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Examples\Policies;

use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;

/**
 * Example: admin routes require phishing-resistant assurance.
 */
final readonly class AdminPasskeyRequiredPolicy
{
    public function execute(): AccessPolicy
    {
        return AccessPolicy::forIdentityPolicy(identityPolicy: IdentityPolicyCatalog::admin());
    }
}
