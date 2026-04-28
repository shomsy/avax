<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Examples\Policies;

use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;

/**
 * Example: host applications can branch on policy for approval workflows.
 */
final readonly class RequireApprovalForPrivilegedAction
{
    public function execute() : bool
    {
        return IdentityPolicyCatalog::admin()->privilegedApprovalRequired;
    }
}
