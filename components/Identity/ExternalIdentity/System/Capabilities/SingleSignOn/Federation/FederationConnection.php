<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class FederationConnection
{
    public FederationConnectionHealth $health;

    /** @var array<string, list<string>> */
    public array $groupRoleMap;

    public bool $ssoOnly;

    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string               $connectionId,
        public string               $tenantSlug,
        public string               $name,
        public FederationProvider   $provider,
        public string                 $domain, bool|null $ssoOnly = null, array|null $groupRoleMap = null,
        public string|null            $metadataUrl = null,
        public string|null            $metadataIssuer = null,
        #[SensitiveParameter]
        public string|null            $metadataHash = null,
        public DateTimeImmutable|null $metadataSyncedAt = null,
        #[SensitiveParameter]
        public string|null            $domainVerificationToken = null,
        public DateTimeImmutable|null $domainVerifiedAt = null, FederationConnectionHealth|null $federationConnectionHealth = null,
        public DateTimeImmutable|null $healthCheckedAt = null,
        public bool                 $breakGlassAllowed = false,
    )
    {
        $ssoOnly                    ??= false;
        $groupRoleMap               ??= [];
        $federationConnectionHealth ??= FederationConnectionHealth::UNKNOWN;
        $this->ssoOnly              = $ssoOnly;
        $this->groupRoleMap         = $groupRoleMap;
        $this->health               = $federationConnectionHealth;
    }

    public function isDomainVerified() : bool
    {
        return $this->domainVerifiedAt instanceof DateTimeImmutable;
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
            healthCheckedAt        : $this->healthCheckedAt,
            breakGlassAllowed      : $this->breakGlassAllowed,
            health                 : $this->health,
        );
    }

    public function withMetadata(
        string            $metadataIssuer,
        #[SensitiveParameter]
        string            $metadataHash,
        DateTimeImmutable $syncedAt,
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
            healthCheckedAt        : $this->healthCheckedAt,
            breakGlassAllowed      : $this->breakGlassAllowed,
            health                 : $this->health,
        );
    }

    public function withHealth(
        FederationConnectionHealth $federationConnectionHealth,
        DateTimeImmutable          $checkedAt,
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
            healthCheckedAt        : $checkedAt,
            breakGlassAllowed      : $this->breakGlassAllowed,
            health                 : $federationConnectionHealth,
        );
    }
}
