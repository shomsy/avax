<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Assembly;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfigurationStoreInterface;
use SensitiveParameter;

/**
 * Configuration assembly graph for tenancy and admin elevation dependencies.
 *
 * Owns: tenant stores, tenant security configuration, admin elevation,
 * and risk engine.
 */
final class TenancyAdministrationGraph
{
    private TenantStoreInterface|null $tenantStore = null;
    private TenantSecurityConfigurationStoreInterface|null $tenantSecurityConfigurationStore = null;
    private TenantSecurityChangeRequestStoreInterface|null $tenantSecurityChangeRequestStore = null;
    private AdminElevationStoreInterface|null $adminElevationStore = null;
    private DeterministicRiskEngine|null $deterministicRiskEngine = null;
    private bool $adminPhishingResistantRequired = false;

    public function withTenantStore(#[SensitiveParameter] TenantStoreInterface $tenantStore) : self
    {
        $this->tenantStore = $tenantStore;

        return $this;
    }

    public function withTenantSecurityConfigurationStore(#[SensitiveParameter] TenantSecurityConfigurationStoreInterface $tenantSecurityConfigurationStore) : self
    {
        $this->tenantSecurityConfigurationStore = $tenantSecurityConfigurationStore;

        return $this;
    }

    public function withTenantSecurityChangeRequestStore(#[SensitiveParameter] TenantSecurityChangeRequestStoreInterface $tenantSecurityChangeRequestStore) : self
    {
        $this->tenantSecurityChangeRequestStore = $tenantSecurityChangeRequestStore;

        return $this;
    }

    public function withAdminElevationStore(AdminElevationStoreInterface $adminElevationStore) : self
    {
        $this->adminElevationStore = $adminElevationStore;

        return $this;
    }

    public function withRiskEngine(DeterministicRiskEngine $deterministicRiskEngine) : self
    {
        $this->deterministicRiskEngine = $deterministicRiskEngine;

        return $this;
    }

    public function requirePhishingResistantAdminElevation(bool $required = true) : self
    {
        $this->adminPhishingResistantRequired = $required;

        return $this;
    }

    /**
     * @return array{
     *     tenantStore: TenantStoreInterface|null,
     *     tenantSecurityConfigurationStore: TenantSecurityConfigurationStoreInterface|null,
     *     tenantSecurityChangeRequestStore: TenantSecurityChangeRequestStoreInterface|null,
     *     adminElevationStore: AdminElevationStoreInterface|null,
     *     deterministicRiskEngine: DeterministicRiskEngine|null,
     *     adminPhishingResistantRequired: bool,
     * }
     */
    public function assemble() : array
    {
        return [
            'tenantStore'                        => $this->tenantStore,
            'tenantSecurityConfigurationStore'   => $this->tenantSecurityConfigurationStore,
            'tenantSecurityChangeRequestStore'   => $this->tenantSecurityChangeRequestStore,
            'adminElevationStore'                => $this->adminElevationStore,
            'deterministicRiskEngine'            => $this->deterministicRiskEngine,
            'adminPhishingResistantRequired'     => $this->adminPhishingResistantRequired,
        ];
    }
}
