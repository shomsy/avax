<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\PublicSurface;

use Avax\Components\Identity\Security\System\Capabilities\Configuration\SecurityConfigurationStore;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApproveSecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\BeginSecurityChange;

/**
 * Security - Main entry point for Identity/Security component.
 */
final readonly class Security implements SecurityInterface
{
    public function __construct(
        private SecurityConfigurationStore $configStore,
        private BeginSecurityChange   $beginChange,
        private ApproveSecurityChange $approveChange,
    ) {}

    public function readConfiguration(string $tenantId) : object
    {
        return $this->configStore->read($tenantId);
    }

    public function beginChange(string $tenantId, array $data) : object
    {
        return $this->beginChange->execute($tenantId, $data);
    }

    public function approveChange(string $requestId) : void
    {
        $this->approveChange->execute($requestId);
    }

    public function applyChange(string $requestId) : void
    {
        // Implementation
    }
}
