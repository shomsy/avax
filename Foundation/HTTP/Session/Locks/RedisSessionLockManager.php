<?php
declare(strict_types=1);

namespace Foundation\HTTP\Session\Locks;

use Redis;

final class RedisSessionLockManager implements SessionLockManagerInterface
{
    private Redis  $redis;
    private string $prefix = 'session_lock:';

    public function __construct(Redis $redis) { $this->redis = $redis; }

    public function acquire(string $sessionId, int $ttl = 5) : bool
    {
        $key = $this->prefix . $sessionId;

        return (bool) $this->redis->set($key, '1', ['nx', 'ex' => $ttl]);
    }

    public function release(string $sessionId) : void
    {
        $this->redis->del([$this->prefix . $sessionId]);
    }

    public function isLocked(string $sessionId) : bool
    {
        return (bool) $this->redis->exists($this->prefix . $sessionId);
    }
}
