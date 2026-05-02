<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Flows\ManageSecurityChange;

use Avax\Components\Identity\Security\System\Capabilities\Configuration\SecurityConfigurationStore;

/**
 * ApproveSecurityChange - Approves a pending security configuration change.
 */
final readonly class ApproveSecurityChange
{
    public function __construct(private SecurityConfigurationStore $securityConfigurationStore) {}

    public function execute(string $requestId) : void
    {
        $this->securityConfigurationStore->approve(requestId: $requestId);
    }
}
