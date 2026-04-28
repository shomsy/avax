<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Examples\Policies;

use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;

/**
 * Example: high-impact admin action requires fresh assurance.
 */
final readonly class RequireFreshAssuranceForAdminAction
{
    public function execute() : AccessPolicy
    {
        return AccessPolicy::forIdentityPolicy(
            identityPolicy    : IdentityPolicyCatalog::admin(),
            requiredPermission: new UserPermission(value: 'billing.refund.approve')
        );
    }
}
