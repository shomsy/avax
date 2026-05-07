<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Webhooks;

use Generator;

final readonly class WebhookRetryPolicy
{
    public function __construct(
        public int $maxAttempts = 3,
        public int $backoffMs = 1000,
        public int $timeout = 10,
    ) {}

    /**
     * @return Generator<int>
     */
    public function attempts() : Generator
    {
        for ($i = 0; $i < $this->maxAttempts; $i++) {
            if ($i > 0) {
                usleep($this->backoffMs * 1000 * $i);
            }

            yield $i;
        }
    }

    public function timeout() : int
    {
        return $this->timeout;
    }

    public function withMaxAttempts(int $attempts) : self
    {
        return new self(
            maxAttempts: $attempts,
            backoffMs  : $this->backoffMs,
            timeout    : $this->timeout,
        );
    }
}
