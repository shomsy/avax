<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Flows\ManageSecurityChange;

use Avax\Components\Identity\Security\System\Capabilities\Configuration\SecurityConfigurationStore;

/**
 * Applies an approved security configuration change to the tenant configuration.
 */
final readonly class ApplySecurityChange
{
    public function __construct(private SecurityConfigurationStore $securityConfigurationStore) {}

    public function execute(string $requestId) : object
    {
        return $this->securityConfigurationStore->apply(requestId: $requestId);
    }
}
