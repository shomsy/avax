<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Model;

use DateTimeImmutable;

final readonly class Tenant
{
    public function __construct(public string $tenantId, public string $slug, public string $name, public int $ownerUserId, public DateTimeImmutable $createdAt) {}
}
