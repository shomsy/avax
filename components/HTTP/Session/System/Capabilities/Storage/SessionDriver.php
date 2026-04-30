<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

use Avax\Components\HTTP\Session\System\Foundation\SessionData;
use Avax\Components\HTTP\Session\System\Foundation\SessionStoreInterface;

final class SessionDriver
{
    private static SessionStoreInterface|null $store = null;

    public static function setStore(SessionStoreInterface $store) : void
    {
        self::$store = $store;
    }

    public static function read(string $sessionId) : SessionData|null
    {
        return self::get()->read($sessionId);
    }

    public static function get() : SessionStoreInterface
    {
        if (self::$store === null) {
            self::$store = self::make();
        }

        return self::$store;
    }

    public static function make(array $config = []) : SessionStoreInterface
    {
        $driver = $config['driver'] ?? 'file';

        return match ($driver) {
            'redis'    => new RedisSessionStore($config['redis'] ?? []),
            'database' => new DatabaseSessionStore($config['database'] ?? []),
            default    => new FileSessionStore($config['file'] ?? []),
        };
    }

    public static function write(string $sessionId, SessionData $data) : bool
    {
        return self::get()->write($sessionId, $data);
    }

    public static function destroy(string $sessionId) : bool
    {
        return self::get()->destroy($sessionId);
    }

    public static function exists(string $sessionId) : bool
    {
        return self::get()->exists($sessionId);
    }
}

final class DatabaseSessionStore implements SessionStoreInterface
{
    public function __construct(
        private array $config = [],
    ) {}

    public function read(string $sessionId) : SessionData|null
    {
        return null;
    }

    public function write(string $sessionId, SessionData $data) : bool
    {
        return true;
    }

    public function destroy(string $sessionId) : bool
    {
        return true;
    }

    public function exists(string $sessionId) : bool
    {
        return false;
    }

    public function gc(int $maxLifetime) : int
    {
        return 0;
    }
}