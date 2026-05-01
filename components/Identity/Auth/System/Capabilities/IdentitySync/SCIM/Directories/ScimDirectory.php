<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class ScimDirectory
{
    public ScimDirectoryHealth $health;

    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string $directoryId,
        public string $tenantSlug,
        public string $name,
        #[SensitiveParameter]
        public string $tokenHash,
        public array $groupRoleMap,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $rotatedAt = null,
        ScimDirectoryHealth $health = null,
        public ?DateTimeImmutable $healthCheckedAt = null,
        public ?string $outageReason = null,
        public ?DateTimeImmutable $outageStartedAt = null,
        public ?DateTimeImmutable $outageRecoveredAt = null,
    ) {
        $health ??= ScimDirectoryHealth::HEALTHY;
        $this->health = $health;
    }

    public function isHealthy(): bool
    {
        return $this->health !== ScimDirectoryHealth::UNAVAILABLE;
    }

    public function markOutage(DateTimeImmutable $startedAt, string $reason = null) : self
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
            outageReason     : trim(string: (string) $reason) !== '' ? trim(string: (string) $reason) : 'scim_outage',
            outageStartedAt  : $startedAt,
            outageRecoveredAt: null,
        );
    }

    public function recover(DateTimeImmutable $recoveredAt): self
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
            outageRecoveredAt: $recoveredAt,
        );
    }
}
