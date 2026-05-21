<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Login\RateLimit;

/**
 * Interface for rate limit storage within the Auth System.
 *
 * Capability: Pluggable storage for brute force protection.
 */
interface LoginRateLimitStorageInterface
{
    public function get(string $identifier) : int;

    public function increment(string $identifier, int $recordedAt) : void;

    public function reset(string $identifier) : void;

    public function getLastAttemptTime(string $identifier) : int;
}
