<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\Lifecycle;

use DateTimeImmutable;

final readonly class LifecycleRecord
{
    public string|null       $reason;
    public DateTimeImmutable $changedAt;
    public LifecycleSource   $source;
    public LifecycleState    $state;
    public int               $userId;

    public function __construct(
        int               $userId,
        LifecycleState    $state,
        LifecycleSource   $source,
        DateTimeImmutable $changedAt,
        string|null       $reason = null
    )
    {
        $this->userId    = $userId;
        $this->state     = $state;
        $this->source    = $source;
        $this->changedAt = $changedAt;
        $this->reason    = $reason;
    }

    public function allowsAuthentication() : bool
    {
        return $this->state === LifecycleState::ACTIVE;
    }
}
