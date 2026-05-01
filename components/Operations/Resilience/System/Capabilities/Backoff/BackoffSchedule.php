<?php

declare(strict_types=1);

namespace Avax\Components\Resilience\System\Capabilities\Backoff;

final readonly class BackoffSchedule
{
    public function __construct(
        private int $baseMilliseconds = 100,
        private int $maximumMilliseconds = 5_000,
    ) {}

    public function delayForAttempt(int $attempt) : int
    {
        $attempt = max(1, $attempt);
        $delay   = $this->baseMilliseconds * (2 ** ($attempt - 1));

        return min($this->maximumMilliseconds, $delay);
    }
}
