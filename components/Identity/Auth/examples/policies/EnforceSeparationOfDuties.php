<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Examples\Policies;

use Avax\Components\Identity\Access\System\Capabilities\Policy\IdentityPolicyCatalog;

/**
 * Example: policy-driven separation of duties gate.
 */
final readonly class EnforceSeparationOfDuties
{
    public function execute(): bool
    {
        return IdentityPolicyCatalog::admin()->separationOfDutiesRequired;
    }
}
