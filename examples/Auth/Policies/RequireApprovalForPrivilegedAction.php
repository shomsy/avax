<?php

declare(strict_types=1);

namespace Avax\Examples\Auth\Policies;

use Avax\Components\Identity\Access\System\Capabilities\Policy\IdentityPolicyCatalog;

final readonly class RequireApprovalForPrivilegedAction
{
    public function execute(): bool
    {
        return IdentityPolicyCatalog::admin()->privilegedApprovalRequired;
    }
}
