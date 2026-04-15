<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\AdminRealm;

use DateTimeImmutable;

final readonly class AdminElevationRecord
{
    public DateTimeImmutable $expiresAt;
    public string            $bindingId;
    public int               $userId;

    public function __construct(
        int               $userId,
        string            $bindingId,
        DateTimeImmutable $expiresAt
    )
    {
        $this->userId    = $userId;
        $this->bindingId = $bindingId;
        $this->expiresAt = $expiresAt;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }
}
