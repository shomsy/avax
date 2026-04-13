<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Scim;

use DateTimeImmutable;

final readonly class ScimDirectory
{
    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string $directoryId,
        public string $tenantSlug,
        public string $name,
        public string $tokenHash,
        public array $groupRoleMap,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable|null $rotatedAt = null
    ) {}
}
