<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime;

use DateTimeImmutable;

final readonly class AdminElevation
{
    public function __construct(public string $bindingId, public DateTimeImmutable $expiresAt)
    {
    }
}
