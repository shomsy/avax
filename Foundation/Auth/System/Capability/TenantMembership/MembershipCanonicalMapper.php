<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantMembership;

use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentity;

/**
 * Maps local membership and federated/SCIM membership into a canonical model.
 *
 * Banal: The Membership Canonical Mapper file.
 */
final readonly class MembershipCanonicalMapper
{
    /**
     * Maps a local user to a canonical membership representation.
     */
    public function mapLocalUser(string $userId, string $tenantId, string $roleId) : CanonicalMembership
    {
        return new CanonicalMembership(
            canonicalId: "local:{$userId}:{$tenantId}",
            userId: $userId,
            tenantId: $tenantId,
            roleId: $roleId,
            source: MembershipSource::LOCAL,
            externalId: null,
            directoryId: null,
            isActive: true,
            syncedAt: new \DateTimeImmutable()
        );
    }

/**
     * Maps a SCIM-provisioned identity to a canonical membership representation.
     */
    public function mapScimIdentity(ScimProvisionedIdentity $identity, string $tenantId) : CanonicalMembership
    {
        return new CanonicalMembership(
            canonicalId: "scim:" . $identity->userId->value . ":{$tenantId}",
            userId: (string) $identity->userId->value,
            tenantId: $tenantId,
            roleId: $this->deriveRoleFromGroups($identity->groups),
            source: MembershipSource::SCIM,
            externalId: $identity->externalId,
            directoryId: $identity->directoryId,
            isActive: $identity->state->value === 'active',
            syncedAt: $identity->synchronizedAt
        );
    }

    /**
     * Maps a federated identity to a canonical membership representation.
     */
    public function mapFederatedIdentity(string $userId, string $tenantId, string $roleId, string $identityProviderId) : CanonicalMembership
    {
        return new CanonicalMembership(
            canonicalId: "fed:{$userId}:{$tenantId}",
            userId: $userId,
            tenantId: $tenantId,
            roleId: $roleId,
            source: MembershipSource::FEDERATED,
            externalId: null,
            directoryId: $identityProviderId,
            isActive: true,
            syncedAt: new \DateTimeImmutable()
        );
    }

    /**
     * @param list<string> $groups
     */
    private function deriveRoleFromGroups(array $groups) : string
    {
        // In a real implementation, this would use the tenant's group-role mapping
        // For now, we default to a member role
        return 'member';
    }
}

/**
 * Represents a canonical membership across all identity sources.
 */
final readonly class CanonicalMembership
{
    public function __construct(
        public readonly string $canonicalId,
        public readonly string $userId,
        public readonly string $tenantId,
        public readonly string $roleId,
        public readonly MembershipSource $source,
        public readonly string|null $externalId,
        public readonly string|null $directoryId,
        public readonly bool $isActive,
        public readonly \DateTimeImmutable $syncedAt
    ) {}
}

/**
 * Represents the source of a membership.
 */
enum MembershipSource: string
{
    case LOCAL = 'local';
    case FEDERATED = 'federated';
    case SCIM = 'scim';
}