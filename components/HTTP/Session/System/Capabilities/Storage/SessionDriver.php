<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

final class SessionDriver
{
    private static ?SessionStoreInterface $store = null;

    public static function setStore(SessionStoreInterface $store) : void
    {
        self::$store = $store;
    }

    public static function read(string $sessionId) : array
    {
        return self::get()->read(id: $sessionId);
    }

    public static function write(string $sessionId, array $data) : bool
    {
        return self::get()->write(id: $sessionId, data: $data);
    }

    public static function destroy(string $sessionId) : bool
    {
        return self::get()->destroy(id: $sessionId);
    }

    public static function exists(string $sessionId) : bool
    {
        $store = self::get();

        return method_exists(object_or_class: $store, method: 'exists')
            ? $store->exists(sessionId: $sessionId)
            : $store->read(id: $sessionId) !== [];
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
            'redis'    => new RedisSessionStore(config: $config['redis'] ?? []),
            'database' => isset($config['database']['pdo'])
                ? new DatabaseSessionStore(pdo: $config['database']['pdo'], table: $config['database']['table'] ?? 'sessions')
                : new ArraySessionStore(),
            'array'    => new ArraySessionStore(),
            default => new FileSessionStore(config: $config['file'] ?? []),
        };
    }
}
