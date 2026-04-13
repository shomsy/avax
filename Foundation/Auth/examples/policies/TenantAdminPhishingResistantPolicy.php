<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Policies;

use Avax\Auth\System\Capability\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capability\Access\Policy\IdentityPolicyCatalog;

/**
 * Example: tenant-admin posture mirrors high-assurance phishing-resistant auth.
 */
final readonly class TenantAdminPhishingResistantPolicy
{
    public function execute() : AccessPolicy
    {
        return AccessPolicy::forIdentityPolicy(IdentityPolicyCatalog::tenantAdmin());
    }
}
