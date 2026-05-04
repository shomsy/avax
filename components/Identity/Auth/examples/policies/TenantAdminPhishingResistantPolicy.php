<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\examples\policies;

use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\Policy\IdentityPolicyCatalog;

/**
 * Example: tenant-admin posture mirrors high-assurance phishing-resistant auth.
 */
final readonly class TenantAdminPhishingResistantPolicy
{
    public function execute(): AccessPolicy
    {
        return AccessPolicy::forIdentityPolicy(identityPolicy: IdentityPolicyCatalog::tenantAdmin());
    }
}
