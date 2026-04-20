<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime;

use DateTimeImmutable;

final readonly class AdminElevation
{
    public DateTimeImmutable $expiresAt;
    public string            $bindingId;

    public function __construct(
        string            $bindingId,
        DateTimeImmutable $expiresAt
    )
    {
        $this->bindingId = $bindingId;
        $this->expiresAt = $expiresAt;
    }
}
