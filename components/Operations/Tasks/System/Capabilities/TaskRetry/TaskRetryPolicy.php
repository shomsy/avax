<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Capabilities\TaskRetry;

final readonly class TaskRetryPolicy
{
    public function __construct(
        public int  $maxAttempts = 3,
        public int  $backoffMs = 1000,
        public bool $exponential = true,
    ) {}

    /**
     * @return list<int>
     */
    public function delays() : array
    {
        $delays = [];

        for ($i = 0; $i < $this->maxAttempts; $i++) {
            $delay = $this->exponential
                ? $this->backoffMs * (2 ** $i)
                : $this->backoffMs;

            $delays[] = $delay;
        }

        return $delays;
    }
}
