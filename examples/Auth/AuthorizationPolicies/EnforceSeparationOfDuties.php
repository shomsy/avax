<?php

declare(strict_types=1);

namespace Avax\Examples\Auth\AuthorizationPolicies;

use Avax\Components\Identity\Access\System\Capabilities\Policy\IdentityPolicyCatalog;

final readonly class EnforceSeparationOfDuties
{
    public function execute(): bool
    {
        return IdentityPolicyCatalog::admin()->separationOfDutiesRequired;
    }
}
