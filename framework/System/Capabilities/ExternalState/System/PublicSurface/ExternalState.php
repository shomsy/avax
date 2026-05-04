<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ExternalState\System\PublicSurface;

use Avax\Framework\System\Capabilities\ExternalState\System\Capabilities\ReadApiDescriptions\Memory;
use Avax\Framework\System\Capabilities\ExternalState\System\Capabilities\ReadApiDescriptions\Redis;

final class ExternalState
{
    private static ?State $session = null;

    private static ?State $cache = null;

    private static ?State $lock = null;

    private static ?State $rateLimit = null;

    public static function session(): State
    {
        if (! self::$session instanceof State) {
            self::$session = self::defaultSessionAdapter();
        }

        return self::$session;
    }

    private static function defaultSessionAdapter(): State
    {
        $url = getenv('REDIS_URL') ?: (getenv('SESSION_STORE') ?: '');

        if ($url !== '' && $url !== '0') {
            return new Redis($url);
        }

        return new Memory();
    }

    public static function cache(): State
    {
        if (! self::$cache instanceof State) {
            self::$cache = self::defaultCacheAdapter();
        }

        return self::$cache;
    }

    private static function defaultCacheAdapter(): State
    {
        $url = getenv('REDIS_URL') ?: (getenv('CACHE_STORE') ?: '');

        if ($url !== '' && $url !== '0') {
            return new Redis($url);
        }

        return new Memory();
    }

    public static function lock(): State
    {
        if (! self::$lock instanceof State) {
            self::$lock = self::defaultLockAdapter();
        }

        return self::$lock;
    }

    private static function defaultLockAdapter(): State
    {
        $url = getenv('REDIS_URL') ?: (getenv('LOCK_STORE') ?: '');

        if ($url !== '' && $url !== '0') {
            return new Redis($url);
        }

        return new Memory();
    }

    public static function rateLimit(): State
    {
        if (! self::$rateLimit instanceof State) {
            self::$rateLimit = self::defaultRateLimitAdapter();
        }

        return self::$rateLimit;
    }

    private static function defaultRateLimitAdapter(): State
    {
        $url = getenv('REDIS_URL') ?: (getenv('RATE_LIMIT_STORE') ?: '');

        if ($url !== '' && $url !== '0') {
            return new Redis($url);
        }

        return new Memory();
    }

    public static function setSession(State $state) : void
    {
        self::$session = $state;
    }

    public static function setCache(State $state) : void
    {
        self::$cache = $state;
    }

    public static function audit(): StateAudit
    {
        return new StateAudit(
            session  : self::adapterType(state: self::session()),
            cache    : self::adapterType(state: self::cache()),
            lock     : self::adapterType(state: self::lock()),
            rateLimit: self::adapterType(state: self::rateLimit()),
        );
    }

    private static function adapterType(?State $state) : string
    {
        if ($state instanceof Redis) {
            return 'Redis';
        }

        if ($state instanceof Memory) {
            return 'Memory';
        }

        return 'Unknown';
    }
}
