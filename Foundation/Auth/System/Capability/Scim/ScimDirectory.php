<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Scim;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class ScimDirectory
{
    public DateTimeImmutable|null $outageRecoveredAt;
    public DateTimeImmutable|null $outageStartedAt;
    public string|null            $outageReason;
    public DateTimeImmutable|null $healthCheckedAt;
    public ScimDirectoryHealth    $health;
    public DateTimeImmutable|null $rotatedAt;
    public DateTimeImmutable      $createdAt;
    public array                  $groupRoleMap;
    public string                 $tokenHash;
    public string                 $name;
    public string                 $tenantSlug;
    public string                 $directoryId;

    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        string                       $directoryId,
        string                       $tenantSlug,
        string                       $name,
        #[SensitiveParameter] string $tokenHash,
        array                        $groupRoleMap,
        DateTimeImmutable            $createdAt,
        DateTimeImmutable|null       $rotatedAt = null,
        ScimDirectoryHealth|null     $health = null,
        DateTimeImmutable|null       $healthCheckedAt = null,
        string|null                  $outageReason = null,
        DateTimeImmutable|null       $outageStartedAt = null,
        DateTimeImmutable|null       $outageRecoveredAt = null
    )
    {
        $health                  ??= ScimDirectoryHealth::HEALTHY;
        $this->directoryId       = $directoryId;
        $this->tenantSlug        = $tenantSlug;
        $this->name              = $name;
        $this->tokenHash         = $tokenHash;
        $this->groupRoleMap      = $groupRoleMap;
        $this->createdAt         = $createdAt;
        $this->rotatedAt         = $rotatedAt;
        $this->health            = $health;
        $this->healthCheckedAt   = $healthCheckedAt;
        $this->outageReason      = $outageReason;
        $this->outageStartedAt   = $outageStartedAt;
        $this->outageRecoveredAt = $outageRecoveredAt;
    }

    public function isHealthy() : bool
    {
        return $this->health !== ScimDirectoryHealth::UNAVAILABLE;
    }

    public function markOutage(DateTimeImmutable $startedAt, string|null $reason = null) : self
    {
        return new self(
            directoryId      : $this->directoryId,
            tenantSlug       : $this->tenantSlug,
            name             : $this->name,
            tokenHash        : $this->tokenHash,
            groupRoleMap     : $this->groupRoleMap,
            createdAt        : $this->createdAt,
            rotatedAt        : $this->rotatedAt,
            health           : ScimDirectoryHealth::UNAVAILABLE,
            healthCheckedAt  : $startedAt,
            outageReason     : trim((string) $reason) !== '' ? trim((string) $reason) : 'scim_outage',
            outageStartedAt  : $startedAt,
            outageRecoveredAt: null
        );
    }

    public function recover(DateTimeImmutable $recoveredAt) : self
    {
        return new self(
            directoryId      : $this->directoryId,
            tenantSlug       : $this->tenantSlug,
            name             : $this->name,
            tokenHash        : $this->tokenHash,
            groupRoleMap     : $this->groupRoleMap,
            createdAt        : $this->createdAt,
            rotatedAt        : $this->rotatedAt,
            health           : ScimDirectoryHealth::HEALTHY,
            healthCheckedAt  : $recoveredAt,
            outageReason     : null,
            outageStartedAt  : $this->outageStartedAt,
            outageRecoveredAt: $recoveredAt
        );
    }
}
