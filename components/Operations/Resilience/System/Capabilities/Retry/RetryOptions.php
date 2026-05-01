<?php

declare(strict_types=1);

namespace Avax\Components\Resilience\System\Capabilities\Retry;

final readonly class RetryOptions
{
    public function __construct(
        public int      $attempts,
        public int      $backoffMs,
        public int|null $timeoutMs = null,
    ) {}

    public function withAttempts(int $attempts) : self
    {
        return new self($attempts, $this->backoffMs, $this->timeoutMs);
    }

    public function withBackoff(int $backoffMs) : self
    {
        return new self($this->attempts, $backoffMs, $this->timeoutMs);
    }
}
