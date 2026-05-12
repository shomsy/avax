<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Retry;

final readonly class RetryOptions
{
    public function __construct(
        public int $attempts,
        public int $backoffMs,
        public int|null $timeoutMs = null,
        public string $backoffStrategy = 'fixed',
        public bool   $jitter = false,
    ) {
    }

    public function withAttempts(int $attempts): self
    {
        return new self($attempts, $this->backoffMs, $this->timeoutMs, $this->backoffStrategy, $this->jitter);
    }

    public function withBackoff(int $backoffMs): self
    {
        return new self($this->attempts, $backoffMs, $this->timeoutMs, $this->backoffStrategy, $this->jitter);
    }

    public function withBackoffStrategy(string $strategy) : self
    {
        return new self($this->attempts, $this->backoffMs, $this->timeoutMs, $strategy, $this->jitter);
    }

    public function withJitter(bool $jitter) : self
    {
        return new self($this->attempts, $this->backoffMs, $this->timeoutMs, $this->backoffStrategy, $jitter);
    }
}
