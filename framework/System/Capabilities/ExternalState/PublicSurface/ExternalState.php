<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ExternalState\PublicSurface;

use Avax\Framework\System\Capabilities\ExternalState\Capabilities\Adapters\MemoryStateAdapter;
use Avax\Framework\System\Capabilities\ExternalState\Capabilities\Adapters\RedisStateAdapter;

final class ExternalState
{
    private static ?StateAdapter $session = null;

    private static ?StateAdapter $cache = null;

    private static ?StateAdapter $lock = null;

    private static ?StateAdapter $rateLimit = null;

    public static function setSession(StateAdapter $stateAdapter): void
    {
        self::$session = $stateAdapter;
    }

    public static function setCache(StateAdapter $stateAdapter): void
    {
        self::$cache = $stateAdapter;
    }

    public static function audit(): StateAudit
    {
        return new StateAudit(
            session: self::adapterType(stateAdapter: self::session()),
            cache: self::adapterType(stateAdapter: self::cache()),
            lock: self::adapterType(stateAdapter: self::lock()),
            rateLimit: self::adapterType(stateAdapter: self::rateLimit()),
        );
    }

    private static function adapterType(?StateAdapter $stateAdapter): string
    {
        if ($stateAdapter instanceof RedisStateAdapter) {
            return 'Redis';
        }

        if ($stateAdapter instanceof MemoryStateAdapter) {
            return 'Memory';
        }

        return 'Unknown';
    }

    public static function session(): StateAdapter
    {
        if (!self::$session instanceof StateAdapter) {
            self::$session = self::defaultSessionAdapter();
        }

        return self::$session;
    }

    private static function defaultSessionAdapter(): StateAdapter
    {
        $url = getenv('REDIS_URL') ?: (getenv('SESSION_STORE') ?: '');

        if ($url !== '' && $url !== '0') {
            return new RedisStateAdapter($url);
        }

        return new MemoryStateAdapter();
    }

    public static function cache(): StateAdapter
    {
        if (!self::$cache instanceof StateAdapter) {
            self::$cache = self::defaultCacheAdapter();
        }

        return self::$cache;
    }

    private static function defaultCacheAdapter(): StateAdapter
    {
        $url = getenv('REDIS_URL') ?: (getenv('CACHE_STORE') ?: '');

        if ($url !== '' && $url !== '0') {
            return new RedisStateAdapter($url);
        }

        return new MemoryStateAdapter();
    }

    public static function lock(): StateAdapter
    {
        if (!self::$lock instanceof StateAdapter) {
            self::$lock = self::defaultLockAdapter();
        }

        return self::$lock;
    }

    private static function defaultLockAdapter(): StateAdapter
    {
        $url = getenv('REDIS_URL') ?: (getenv('LOCK_STORE') ?: '');

        if ($url !== '' && $url !== '0') {
            return new RedisStateAdapter($url);
        }

        return new MemoryStateAdapter();
    }

    public static function rateLimit(): StateAdapter
    {
        if (!self::$rateLimit instanceof StateAdapter) {
            self::$rateLimit = self::defaultRateLimitAdapter();
        }

        return self::$rateLimit;
    }

    private static function defaultRateLimitAdapter(): StateAdapter
    {
        $url = getenv('REDIS_URL') ?: (getenv('RATE_LIMIT_STORE') ?: '');

        if ($url !== '' && $url !== '0') {
            return new RedisStateAdapter($url);
        }

        return new MemoryStateAdapter();
    }
}
