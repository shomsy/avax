<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\examples\policies;

use Avax\Components\Identity\Access\System\Capabilities\Policy\IdentityPolicyCatalog;

/**
 * Example: host applications can branch on policy for approval workflows.
 */
final readonly class RequireApprovalForPrivilegedAction
{
    public function execute(): bool
    {
        return IdentityPolicyCatalog::admin()->privilegedApprovalRequired;
    }
}
