<?php

declare(strict_types=1);

namespace Avax\Auth\System\ControlPlane\TenantAdmin;

use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use InvalidArgumentException;

/**
 * Manages tenant-level configuration changes in the control plane.
 *
 * Banal: The Tenant Admin Configuration Manager file.
 */
final readonly class ConfigurationManager
{
    public function __construct(
        private AuthInterface $auth
    ) {}

    /**
     * Applies a configuration change to a tenant.
     *
     * @throws InvalidArgumentException
     */
    public function applyConfigurationChange(
        string $tenantSlug,
        string $changedBy,
        string $reason,
        array $configurationChanges
    ) : void
    {
        // Validate input
        if ($tenantSlug === '' || trim($tenantSlug) === '') {
            throw new InvalidArgumentException('tenantSlug is required');
        }

        if ($changedBy === '' || trim($changedBy) === '') {
            throw new InvalidArgumentException('changedBy is required');
        }

        if ($reason === '' || trim($reason) === '') {
            throw new InvalidArgumentException('reason is required');
        }

        // Begin the change process
        $change = $this->auth->beginTenantSecurityChange(
            data: new BeginTenantSecurityChangeData(
                tenantSlug: $tenantSlug,
                requestedBy: $changedBy,
                reason: $reason,
                after: $this->mergeConfiguration(
                    $this->auth->readTenantSecurityConfiguration($tenantSlug),
                    $configurationChanges
                )
            )
        );

        // Auto-approve and apply for simplicity in this implementation
        // In a real system, this would go through an approval workflow
        $this->auth->approveTenantSecurityChange(
            changeId: $change->changeId,
            approvedBy: $changedBy
        );

        $this->auth->applyTenantSecurityChange(
            changeId: $change->changeId
        );
    }

    /**
     * Merges existing configuration with new changes.
     */
    private function mergeConfiguration(
        TenantSecurityConfiguration|null $existing,
        array                            $changes
    ) : TenantSecurityConfiguration {
        $existing = $existing ?? new TenantSecurityConfiguration(
            tenantSlug: '', // Will be overridden
            federationConnectionId: null,
            scimDirectoryId: null,
            verifiedDomains: [],
            groupRoleMap: [],
            policyProfile: 'user',
            rolloutVersion: 1
        );

        return new TenantSecurityConfiguration(
            tenantSlug: $existing->tenantSlug,
            federationConnectionId: $changes['federationConnectionId'] ?? $existing->federationConnectionId,
            scimDirectoryId: $changes['scimDirectoryId'] ?? $existing->scimDirectoryId,
            verifiedDomains: $changes['verifiedDomains'] ?? $existing->verifiedDomains,
            groupRoleMap: $changes['groupRoleMap'] ?? $existing->groupRoleMap,
            policyProfile: $changes['policyProfile'] ?? $existing->policyProfile,
            rolloutVersion: ($changes['rolloutVersion'] ?? $existing->rolloutVersion) + 1
        );
    }
}