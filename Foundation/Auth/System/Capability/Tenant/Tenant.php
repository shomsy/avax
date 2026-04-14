<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Tenant;

use DateTimeImmutable;

final readonly class Tenant
{
    public function __construct(
        public string $tenantId,
        public string $slug,
        public string $name,
        public int $ownerUserId,
        public DateTimeImmutable $createdAt
    ) {}
}
