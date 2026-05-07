<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Lock;

interface Lock
{
    public function acquire(string $key, int $ttlSeconds = 30) : bool;

    public function release(string $key) : void;

    public function isAcquired(string $key) : bool;
}
