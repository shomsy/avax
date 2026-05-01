<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Flows\ManageSecurityChange;

use Avax\Framework\Foundation\Exception\NotImplementedException;

/**
 * BeginSecurityChange - Initiates a sensitive security configuration change (e.g. changing password policy).
 */
final readonly class BeginSecurityChange
{
    public function execute() : object
    {
        throw new NotImplementedException('Security change workflow not yet implemented');
    }
}
