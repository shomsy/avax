<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

use DateTimeImmutable;

final readonly class FederationConnection
{
    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string $connectionId,
        public string $tenantSlug,
        public string $name,
        public FederationProvider $provider,
        public string $domain,
        public bool $ssoOnly = false,
        public array $groupRoleMap = [],
        public string|null $metadataUrl = null,
        public string|null $metadataIssuer = null,
        public string|null $metadataHash = null,
        public DateTimeImmutable|null $metadataSyncedAt = null,
        public string|null $domainVerificationToken = null,
        public DateTimeImmutable|null $domainVerifiedAt = null,
        public FederationConnectionHealth $health = FederationConnectionHealth::UNKNOWN,
        public DateTimeImmutable|null $healthCheckedAt = null,
        public bool $breakGlassAllowed = false
    ) {}

    public function isDomainVerified() : bool
    {
        return $this->domainVerifiedAt !== null;
    }

    public function withVerifiedDomain(DateTimeImmutable $verifiedAt) : self
    {
        return new self(
            connectionId           : $this->connectionId,
            tenantSlug             : $this->tenantSlug,
            name                   : $this->name,
            provider               : $this->provider,
            domain                 : $this->domain,
            ssoOnly                : $this->ssoOnly,
            groupRoleMap           : $this->groupRoleMap,
            metadataUrl            : $this->metadataUrl,
            metadataIssuer         : $this->metadataIssuer,
            metadataHash           : $this->metadataHash,
            metadataSyncedAt       : $this->metadataSyncedAt,
            domainVerificationToken: $this->domainVerificationToken,
            domainVerifiedAt       : $verifiedAt,
            health                 : $this->health,
            healthCheckedAt        : $this->healthCheckedAt,
            breakGlassAllowed      : $this->breakGlassAllowed
        );
    }

    public function withMetadata(
        string $metadataIssuer,
        string $metadataHash,
        DateTimeImmutable $syncedAt
    ) : self
    {
        return new self(
            connectionId           : $this->connectionId,
            tenantSlug             : $this->tenantSlug,
            name                   : $this->name,
            provider               : $this->provider,
            domain                 : $this->domain,
            ssoOnly                : $this->ssoOnly,
            groupRoleMap           : $this->groupRoleMap,
            metadataUrl            : $this->metadataUrl,
            metadataIssuer         : $metadataIssuer,
            metadataHash           : $metadataHash,
            metadataSyncedAt       : $syncedAt,
            domainVerificationToken: $this->domainVerificationToken,
            domainVerifiedAt       : $this->domainVerifiedAt,
            health                 : $this->health,
            healthCheckedAt        : $this->healthCheckedAt,
            breakGlassAllowed      : $this->breakGlassAllowed
        );
    }

    public function withHealth(
        FederationConnectionHealth $health,
        DateTimeImmutable $checkedAt
    ) : self
    {
        return new self(
            connectionId           : $this->connectionId,
            tenantSlug             : $this->tenantSlug,
            name                   : $this->name,
            provider               : $this->provider,
            domain                 : $this->domain,
            ssoOnly                : $this->ssoOnly,
            groupRoleMap           : $this->groupRoleMap,
            metadataUrl            : $this->metadataUrl,
            metadataIssuer         : $this->metadataIssuer,
            metadataHash           : $this->metadataHash,
            metadataSyncedAt       : $this->metadataSyncedAt,
            domainVerificationToken: $this->domainVerificationToken,
            domainVerifiedAt       : $this->domainVerifiedAt,
            health                 : $health,
            healthCheckedAt        : $checkedAt,
            breakGlassAllowed      : $this->breakGlassAllowed
        );
    }
}
