<?php

declare(strict_types=1);

namespace Avax\Auth\System\UI\TenantAdmin;

use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use SensitiveParameter;

/**
 * Tenant admin dashboard providing overview of tenant status.
 *
 * Banal: The Tenant Admin Dashboard file.
 */
final readonly class Dashboard
{
    public function __construct(#[SensitiveParameter] private AuthInterface $auth) {}

    /**
     * Gets dashboard data for a tenant.
     */
    public function getDashboardData(string $tenantSlug) : array
    {
        $configuration = $this->auth->readTenantSecurityConfiguration($tenantSlug);
        $connections = $this->getTenantConnections($tenantSlug);
        $directories = $this->auth->readScimDirectories($tenantSlug);
        $changes = $this->auth->readTenantSecurityChangeRequests($tenantSlug);

        return [
            'tenant' => $tenantSlug,
            'configuration' => $this->formatConfiguration($configuration),
            'federationConnections' => array_map([$this, 'formatConnection'], $connections),
            'scimDirectories' => array_map([$this, 'formatDirectory'], $directories),
            'recentChanges' => array_map([$this, 'formatChange'], array_slice($changes, 0, 5)),
            'summary' => [
                'totalFederationConnections' => count($connections),
                'totalScimDirectories' => count($directories),
                'pendingChanges' => count(array_filter($changes, fn($c) => $c->status->value === 'pending')),
                'approvedChanges' => count(array_filter($changes, fn($c) => $c->status->value === 'approved')),
            ]
        ];
    }

    private function getTenantConnections(string $tenantSlug) : array
    {
        return array_values(array_filter(
            $this->auth->readFederationConnections(),
            static fn($connection) => $connection->tenantSlug === $tenantSlug
        ));
    }

    private function formatConfiguration(TenantSecurityConfiguration|null $configuration) : array|null
    {
        if ($configuration === null) {
            return null;
        }

        return [
            'tenantSlug' => $configuration->tenantSlug,
            'federationConnectionId' => $configuration->federationConnectionId,
            'scimDirectoryId' => $configuration->scimDirectoryId,
            'verifiedDomains' => $configuration->verifiedDomains,
            'groupRoleMap' => $configuration->groupRoleMap,
            'policyProfile' => $configuration->policyProfile,
            'rolloutVersion' => $configuration->rolloutVersion,
        ];
    }

    private function formatConnection(object $connection) : array
    {
        return [
            'connectionId' => $connection->connectionId,
            'name' => $connection->name,
            'provider' => $connection->provider->value,
            'domain' => $connection->domain,
            'ssoOnly' => $connection->ssoOnly,
            'health' => $connection->health->value,
            'healthCheckedAt' => $connection->healthCheckedAt?->format('c'),
            'breakGlassAllowed' => $connection->breakGlassAllowed,
        ];
    }

    private function formatDirectory(object $directory) : array
    {
        return [
            'directoryId' => $directory->directoryId,
            'name' => $directory->name,
            'createdAt' => $directory->createdAt->format('c'),
            'rotatedAt' => $directory->rotatedAt?->format('c'),
        ];
    }

    private function formatChange(object $change) : array
    {
        return [
            'changeId' => $change->changeId,
            'requestedBy' => $change->requestedBy,
            'reason' => $change->reason,
            'status' => $change->status->value,
            'requestedAt' => $change->requestedAt->format('c'),
            'approvedBy' => $change->approvedBy,
            'approvedAt' => $change->approvedAt?->format('c'),
            'appliedAt' => $change->appliedAt?->format('c'),
            'rolledBackAt' => $change->rolledBackAt?->format('c'),
        ];
    }
}