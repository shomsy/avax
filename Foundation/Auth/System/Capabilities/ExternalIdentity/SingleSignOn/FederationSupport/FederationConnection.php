<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class FederationConnection
{
    public bool                       $breakGlassAllowed;
    public DateTimeImmutable|null     $healthCheckedAt;
    public FederationConnectionHealth $health;
    public DateTimeImmutable|null     $domainVerifiedAt;
    public string|null                $domainVerificationToken;
    public DateTimeImmutable|null     $metadataSyncedAt;
    public string|null                $metadataHash;
    public string|null                $metadataIssuer;
    public string|null                $metadataUrl;
    /** @var array<string, list<string>> */
    public array                      $groupRoleMap;
    public bool                       $ssoOnly;
    public string                     $domain;
    public FederationProvider         $provider;
    public string                     $name;
    public string                     $tenantSlug;
    public string                     $connectionId;

    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        string                            $connectionId,
        string                            $tenantSlug,
        string                            $name,
        FederationProvider                $provider,
        string                            $domain,
        bool|null                         $ssoOnly = null,
        array|null                        $groupRoleMap = null,
        string|null                       $metadataUrl = null,
        string|null                       $metadataIssuer = null,
        #[SensitiveParameter] string|null $metadataHash = null,
        DateTimeImmutable|null            $metadataSyncedAt = null,
        #[SensitiveParameter] string|null $domainVerificationToken = null,
        DateTimeImmutable|null            $domainVerifiedAt = null,
        FederationConnectionHealth|null   $health = null,
        DateTimeImmutable|null            $healthCheckedAt = null,
        bool                              $breakGlassAllowed = false
    )
    {
        $ssoOnly                       ??= false;
        $groupRoleMap                  ??= [];
        $health                        ??= FederationConnectionHealth::UNKNOWN;
        $this->connectionId            = $connectionId;
        $this->tenantSlug              = $tenantSlug;
        $this->name                    = $name;
        $this->provider                = $provider;
        $this->domain                  = $domain;
        $this->ssoOnly                 = $ssoOnly;
        $this->groupRoleMap            = $groupRoleMap;
        $this->metadataUrl             = $metadataUrl;
        $this->metadataIssuer          = $metadataIssuer;
        $this->metadataHash            = $metadataHash;
        $this->metadataSyncedAt        = $metadataSyncedAt;
        $this->domainVerificationToken = $domainVerificationToken;
        $this->domainVerifiedAt        = $domainVerifiedAt;
        $this->health                  = $health;
        $this->healthCheckedAt         = $healthCheckedAt;
        $this->breakGlassAllowed       = $breakGlassAllowed;
    }

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
        string                       $metadataIssuer,
        #[SensitiveParameter] string $metadataHash,
        DateTimeImmutable            $syncedAt
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
        DateTimeImmutable          $checkedAt
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
