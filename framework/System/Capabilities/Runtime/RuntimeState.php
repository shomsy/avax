<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use DateTimeImmutable;

final class RuntimeState
{
    private bool $booted = false;

    private int $bootCount = 0;

    private DateTimeImmutable|null $bootedAt = null;

    private DateTimeImmutable|null $shutdownAt = null;

    public function __construct(private readonly string $runtimeName)
    {
    }

    public function runtimeName(): string
    {
        return $this->runtimeName;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function bootCount(): int
    {
        return $this->bootCount;
    }

    public function bootedAt(): DateTimeImmutable|null
    {
        return $this->bootedAt;
    }

    public function shutdownAt(): DateTimeImmutable|null
    {
        return $this->shutdownAt;
    }

    public function markBooted(DateTimeImmutable $bootedAt): void
    {
        $this->booted = true;
        $this->bootCount += 1;
        $this->bootedAt   = $bootedAt;
        $this->shutdownAt = null;
    }

    public function markShutdown(DateTimeImmutable $shutdownAt): void
    {
        $this->booted     = false;
        $this->shutdownAt = $shutdownAt;
    }
}
