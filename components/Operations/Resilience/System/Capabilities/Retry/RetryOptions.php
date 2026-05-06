<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Retry;

final readonly class RetryOptions
{
    public function __construct(
        public int $attempts,
        public int $backoffMs,
        public ?int $timeoutMs = null,
    ) {
    }

    public function withAttempts(int $attempts): self
    {
        return new self($attempts, $this->backoffMs, $this->timeoutMs);
    }

    public function withBackoff(int $backoffMs): self
    {
        return new self($this->attempts, $backoffMs, $this->timeoutMs);
    }
}
