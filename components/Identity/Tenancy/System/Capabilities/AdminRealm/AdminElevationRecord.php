<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm;

use DateTimeImmutable;

final readonly class AdminElevationRecord
{
    public function __construct(public int $userId, public string $bindingId, public DateTimeImmutable $expiresAt)
    {
    }

    public function isExpiredAt(DateTimeImmutable $moment): bool
    {
        return $this->expiresAt <= $moment;
    }
}
