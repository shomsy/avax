<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Flows\ManageSecurityChange;

use Avax\Components\Identity\Security\System\Capabilities\Configuration\SecurityConfigurationStore;

/**
 * BeginSecurityChange - Initiates a sensitive security configuration change (e.g. changing password policy).
 */
final readonly class BeginSecurityChange
{
    public function __construct(private SecurityConfigurationStore $securityConfigurationStore) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $tenantId, array $data) : \stdClass
    {
        return $this->securityConfigurationStore->begin(tenantId: $tenantId, data: $data);
    }
}
