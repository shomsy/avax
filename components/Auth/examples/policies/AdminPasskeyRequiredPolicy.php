<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Policies;

use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;

/**
 * Example: admin routes require phishing-resistant assurance.
 */
final readonly class AdminPasskeyRequiredPolicy
{
    public function execute() : AccessPolicy
    {
        return AccessPolicy::forIdentityPolicy(identityPolicy: IdentityPolicyCatalog::admin());
    }
}
