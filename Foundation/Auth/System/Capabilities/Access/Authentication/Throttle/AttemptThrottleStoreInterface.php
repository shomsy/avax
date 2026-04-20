<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Throttle;

/**
 * Stores auth-sensitive attempt counts by ownership key.
 */
interface AttemptThrottleStoreInterface
{
    public function get(string $key) : int;

    public function increment(string $key, int $timestamp) : void;

    public function reset(string $key) : void;

    public function getLastAttemptTime(string $key) : int;
}
