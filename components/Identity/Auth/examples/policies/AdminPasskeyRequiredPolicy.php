<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\examples\policies;

use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\Policy\IdentityPolicyCatalog;

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
