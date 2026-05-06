<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle;

use DateTimeImmutable;

final readonly class LifecycleRecord
{
    public function __construct(public int $userId, public LifecycleState $state, public LifecycleSource $source, public DateTimeImmutable $changedAt, public ?string $reason = null)
    {
    }

    public function allowsAuthentication(): bool
    {
        return $this->state === LifecycleState::ACTIVE;
    }
}
