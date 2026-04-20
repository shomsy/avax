<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge;

/**
 * Stores MFA verification attempt counts by key.
 */
interface AttemptLimitStorageInterface
{
    public function get(string $key) : int;

    public function increment(string $key, int $timestamp) : void;

    public function reset(string $key) : void;

    public function getLastAttemptTime(string $key) : int;
}
