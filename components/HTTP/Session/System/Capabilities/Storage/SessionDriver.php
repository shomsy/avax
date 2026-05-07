<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

final class SessionDriver
{
    private static ?SessionStoreInterface $sessionStore = null;

    public static function setStore(SessionStoreInterface $store) : void
    {
        self::$sessionStore = $store;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function write(string $sessionId, array $data) : bool
    {
        return self::get()->write(id: $sessionId, data: $data);
    }

    public static function get() : SessionStoreInterface
    {
        if (! self::$sessionStore instanceof SessionStoreInterface) {
            self::$sessionStore = self::make();
        }

        return self::$sessionStore;
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function make(array $config = []) : SessionStoreInterface
    {
        $driver = $config['driver'] ?? 'file';

        return match ($driver) {
            'redis'    => new RedisSessionStore(config: $config['redis'] ?? []),
            'database' => isset($config['database']['pdo'])
                ? new DatabaseSessionStore(pdo: $config['database']['pdo'], table: $config['database']['table'] ?? 'sessions')
                : new ArraySessionStore(),
            'array'    => new ArraySessionStore(),
            default    => new FileSessionStore(config: $config['file'] ?? []),
        };
    }

    public static function destroy(string $sessionId) : bool
    {
        return self::get()->destroy(id: $sessionId);
    }

    public static function exists(string $sessionId) : bool
    {
        $sessionStore = self::get();

        return method_exists(object_or_class: $sessionStore, method: 'exists')
            ? $sessionStore->exists($sessionId)
            : $sessionStore->read(id: $sessionId) !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function read(string $sessionId) : array
    {
        return self::get()->read(id: $sessionId);
    }
}
