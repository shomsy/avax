<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Lock;

final class InMemoryLock implements Lock
{
    /**
     * @var array<string, int>
     */
    private array $locks = [];

    public function acquire(string $key, int $ttlSeconds = 30) : bool
    {
        if (isset($this->locks[$key]) && $this->locks[$key] > time()) {
            return false;
        }

        $this->locks[$key] = time() + $ttlSeconds;

        return true;
    }

    public function release(string $key) : void
    {
        unset($this->locks[$key]);
    }

    public function isAcquired(string $key) : bool
    {
        return isset($this->locks[$key]) && $this->locks[$key] > time();
    }

    public function releaseExpired() : void
    {
        $now = time();

        foreach ($this->locks as $key => $expiresAt) {
            if ($expiresAt <= $now) {
                unset($this->locks[$key]);
            }
        }
    }
}
