<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Examples\Policies;

use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;

/**
 * Example: tenant-admin posture mirrors high-assurance phishing-resistant auth.
 */
final readonly class TenantAdminPhishingResistantPolicy
{
    public function execute() : AccessPolicy
    {
        return AccessPolicy::forIdentityPolicy(identityPolicy: IdentityPolicyCatalog::tenantAdmin());
    }
}
