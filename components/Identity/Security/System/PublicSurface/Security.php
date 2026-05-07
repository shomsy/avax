<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\PublicSurface;

use Avax\Components\Identity\Security\System\Capabilities\Configuration\SecurityConfigurationStore;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApplySecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApproveSecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\BeginSecurityChange;
use stdClass;

/**
 * Security - Main entry point for Identity/Security component.
 */
final readonly class Security implements SecurityInterface
{
    public function __construct(
        private SecurityConfigurationStore $securityConfigurationStore,
        private BeginSecurityChange        $beginSecurityChange,
        private ApproveSecurityChange      $approveSecurityChange,
        private ApplySecurityChange        $applySecurityChange,
    ) {}

    public function readConfiguration(string $tenantId) : stdClass
    {
        return $this->securityConfigurationStore->read($tenantId);
    }

    public function beginChange(string $tenantId, array $data) : stdClass
    {
        return $this->beginSecurityChange->execute($tenantId, $data);
    }

    public function approveChange(string $requestId) : void
    {
        $this->approveSecurityChange->execute($requestId);
    }

    public function applyChange(string $requestId) : void
    {
        $this->applySecurityChange->execute(requestId: $requestId);
    }
}
