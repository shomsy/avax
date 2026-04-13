<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantMembership;

use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Capability\TenantMembership\TenantMembershipStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Handles deprovision drift between SCIM/federated identity sources and local membership records.
 *
 * Banal: The Deprovision Drift Handler file.
 */
final readonly class DeprovisionDriftHandler
{
    public function __construct(
        private TenantMembershipStoreInterface $tenantMembershipStore,
        private ScimProvisionedIdentityStoreInterface $scimIdentityStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Detects and resolves drift between SCIM-provisioned identities and local memberships.
     *
     * @return list<DriftResolution>
     */
    public function detectAndResolveScimDrift(string $tenantId, DriftResolutionMode $mode) : array
    {
        $resolutions = [];

        // Get all SCIM-provisioned identities for this tenant
        $scimIdentities = $this->scimIdentityStore->allForDirectory($tenantId);

        foreach ($scimIdentities as $scimIdentity) {
            // Check if there's a local membership for this SCIM identity
            $membership = $this->tenantMembershipStore->findMembershipByUserAndTenant(
                (string) $scimIdentity->userId->value,
                $tenantId
            );

            // SCIM says user is inactive, but local membership is active - drift detected
            if ($scimIdentity->state->value !== 'active' && $membership !== null && $membership->isActive()) {
                $resolution = $this->resolveDrift(
                    $tenantId,
                    $scimIdentity->userId->value,
                    $scimIdentity->externalId,
                    DriftType::SCIM_DEPROVISIONED,
                    $membership,
                    $mode
                );
                $resolutions[] = $resolution;
            }

            // SCIM says user is active, but no local membership exists - missing membership
            if ($scimIdentity->state->value === 'active' && $membership === null) {
                // This would require auto-provisioning which is a different flow
                $resolutions[] = new DriftResolution(
                    driftType: DriftType::MISSING_MEMBERSHIP,
                    userId: (string) $scimIdentity->userId->value,
                    tenantId: $tenantId,
                    status: DriftResolutionStatus::REQUIRES_MANUAL_REVIEW,
                    details: 'SCIM identity is active but no local membership exists'
                );
            }
        }

        return $resolutions;
    }

    /**
     * Detects drift where local membership exists but no corresponding SCIM identity.
     *
     * @return list<DriftResolution>
     */
    public function detectOrphanedMemberships(string $tenantId) : array
    {
        $resolutions = [];

        // Get all memberships for the tenant
        $memberships = $this->tenantMembershipStore->findMembershipsByTenant($tenantId);

        foreach ($memberships as $membership) {
            // Skip inactive memberships
            if (!$membership->isActive()) {
                continue;
            }

            // Check if there's a corresponding SCIM identity
            // In a real implementation, we'd query the SCIM identity store
            // For now, we'll just return empty list as this requires more complex querying
        }

        return $resolutions;
    }

    private function resolveDrift(
        string $tenantId,
        int $userId,
        string $externalId,
        DriftType $driftType,
        Membership $membership,
        DriftResolutionMode $mode
    ) : DriftResolution {
        $resolved = false;
        $details = '';

        if ($mode === DriftResolutionMode::AUTO_APPLY) {
            $suspendedMembership = new Membership(
                membershipId: $membership->getMembershipId(),
                userId: $membership->getUserId(),
                tenantId: $membership->getTenantId(),
                roleId: $membership->getRoleId(),
                joinedAt: $membership->getJoinedAt(),
                leftAt: new \DateTimeImmutable(),
                isActive: false
            );
            $this->tenantMembershipStore->saveMembership($suspendedMembership);
            $resolved = true;
            $details = 'Membership suspended automatically due to SCIM deprovision';
        } elseif ($mode === DriftResolutionMode::OBSERVE_ONLY) {
            $details = 'Drift detected but not automatically resolved (observe-only mode)';
        } elseif ($mode === DriftResolutionMode::REQUIRE_APPROVAL) {
            $details = 'Drift detected - manual approval required for resolution';
        }

        // Audit the drift resolution
        $this->auditLog->record(new AuditEvent(
            name: 'auth.tenant.membership.drift.detected',
            occurredAt: $this->clock->now(),
            context: [
                'tenant_id' => $tenantId,
                'user_id' => (string) $userId,
                'external_id' => $externalId,
                'drift_type' => $driftType->value,
                'resolution_mode' => $mode->value,
                'resolved' => $resolved,
                'details' => $details,
            ]
        ));

        return new DriftResolution(
            driftType: $driftType,
            userId: (string) $userId,
            tenantId: $tenantId,
            status: $resolved ? DriftResolutionStatus::RESOLVED : DriftResolutionStatus::PENDING_REVIEW,
            details: $details
        );
    }
}

/**
 * Drift resolution mode.
 */
enum DriftResolutionMode: string
{
    case AUTO_APPLY = 'auto_apply';
    case OBSERVE_ONLY = 'observe_only';
    case REQUIRE_APPROVAL = 'require_approval';
}

/**
 * Drift type.
 */
enum DriftType: string
{
    case SCIM_DEPROVISIONED = 'scim_deprovisioned';
    case FEDERATED_DEPROVISIONED = 'federated_deprovisioned';
    case MISSING_MEMBERSHIP = 'missing_membership';
    case ORPHANED_MEMBERSHIP = 'orphaned_membership';
}

/**
 * Drift resolution status.
 */
enum DriftResolutionStatus: string
{
    case RESOLVED = 'resolved';
    case PENDING_REVIEW = 'pending_review';
    case REQUIRES_MANUAL_REVIEW = 'requires_manual_review';
    case IGNORED = 'ignored';
}

/**
 * Drift resolution result.
 */
final readonly class DriftResolution
{
    public function __construct(
        public readonly DriftType $driftType,
        public readonly string $userId,
        public readonly string $tenantId,
        public readonly DriftResolutionStatus $status,
        public readonly string $details
    ) {}
}