<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

interface RateLimiterInterface
{
    public function canAttempt(string $key, int $maxAttempts, int $decaySeconds) : bool;

    public function recordFailedAttempt(string $key, int $maxAttempts, int $decaySeconds) : void;

    public function remainingAttempts(string $key, int $maxAttempts, int $decaySeconds) : int;

    public function availableIn(string $key, int $maxAttempts, int $decaySeconds) : int;

    public function clear(string $key) : void;
}
