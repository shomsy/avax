<?php

declare(strict_types=1);

namespace Avax\Components\ExternalState\System\PublicSurface;

use Avax\Components\ExternalState\System\Capabilities\Adapters\MemoryStateAdapter;
use Avax\Components\ExternalState\System\Capabilities\Adapters\RedisStateAdapter;

interface StateAdapter
{
    public function get(string $key) : mixed;

    public function set(string $key, mixed $value, int $ttl = 0) : void;

    public function delete(string $key) : void;

    public function exists(string $key) : bool;

    public function increment(string $key, int $value = 1) : int;

    public function expire(string $key, int $ttl) : void;
}

final class ExternalState
{
    private static StateAdapter $session;
    private static StateAdapter $cache;
    private static StateAdapter $lock;
    private static StateAdapter $rateLimit;

    public static function session() : StateAdapter
    {
        if (! isset(self::$session)) {
            self::$session = self::defaultSessionAdapter();
        }

        return self::$session;
    }

    private static function defaultSessionAdapter() : StateAdapter
    {
        $url = getenv('REDIS_URL') ?: (getenv('SESSION_STORE') ?: '');

        if ($url) {
            return new RedisStateAdapter($url);
        }

        return new MemoryStateAdapter();
    }

    public static function cache() : StateAdapter
    {
        if (! isset(self::$cache)) {
            self::$cache = self::defaultCacheAdapter();
        }

        return self::$cache;
    }

    private static function defaultCacheAdapter() : StateAdapter
    {
        $url = getenv('REDIS_URL') ?: (getenv('CACHE_STORE') ?: '');

        if ($url) {
            return new RedisStateAdapter($url);
        }

        return new MemoryStateAdapter();
    }

    public static function lock() : StateAdapter
    {
        if (! isset(self::$lock)) {
            self::$lock = self::defaultLockAdapter();
        }

        return self::$lock;
    }

    private static function defaultLockAdapter() : StateAdapter
    {
        $url = getenv('REDIS_URL') ?: (getenv('LOCK_STORE') ?: '');

        if ($url) {
            return new RedisStateAdapter($url);
        }

        return new MemoryStateAdapter();
    }

    public static function rateLimit() : StateAdapter
    {
        if (! isset(self::$rateLimit)) {
            self::$rateLimit = self::defaultRateLimitAdapter();
        }

        return self::$rateLimit;
    }

    private static function defaultRateLimitAdapter() : StateAdapter
    {
        $url = getenv('REDIS_URL') ?: (getenv('RATE_LIMIT_STORE') ?: '');

        if ($url) {
            return new RedisStateAdapter($url);
        }

        return new MemoryStateAdapter();
    }

    public static function setSession(StateAdapter $adapter) : void
    {
        self::$session = $adapter;
    }

    public static function setCache(StateAdapter $adapter) : void
    {
        self::$cache = $adapter;
    }

    public static function audit() : StateAudit
    {
        return new StateAudit(
            session  : self::adapterType(self::$session),
            cache    : self::adapterType(self::$cache),
            lock     : self::adapterType(self::$lock),
            rateLimit: self::adapterType(self::$rateLimit),
        );
    }

    private static function adapterType(StateAdapter|null $adapter) : string
    {
        if ($adapter instanceof RedisStateAdapter) {
            return 'Redis';
        }

        if ($adapter instanceof MemoryStateAdapter) {
            return 'Memory';
        }

        return 'Unknown';
    }
}

final readonly class StateAudit
{
    public function __construct(
        public string $session,
        public string $cache,
        public string $lock,
        public string $rateLimit,
    ) {}

    public function toArray() : array
    {
        return [
            'session'    => $this->session,
            'cache'      => $this->cache,
            'lock'       => $this->lock,
            'rate_limit' => $this->rateLimit,
        ];
    }

    public function isHorizontalReady() : bool
    {
        return $this->session === 'Redis'
            && $this->cache === 'Redis'
            && $this->lock === 'Redis'
            && $this->rateLimit === 'Redis';
    }
}