<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Flows\ManageSecurityChange;

use Avax\Framework\Foundation\Exception\NotImplementedException;

/**
 * ApproveSecurityChange - Approves a pending security configuration change.
 */
final readonly class ApproveSecurityChange
{
    public function execute(string $requestId): void
    {
        throw new NotImplementedException('Security change approval workflow not yet implemented');
    }
}
