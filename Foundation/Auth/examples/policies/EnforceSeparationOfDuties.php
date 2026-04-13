<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Policies;

use Avax\Auth\System\Capability\Access\Policy\IdentityPolicyCatalog;

/**
 * Example: policy-driven separation of duties gate.
 */
final readonly class EnforceSeparationOfDuties
{
    public function execute() : bool
    {
        return IdentityPolicyCatalog::admin()->separationOfDutiesRequired;
    }
}
