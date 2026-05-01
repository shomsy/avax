<?php

declare(strict_types=1);

namespace Avax\Components\Security\Secrets\System\PublicSurface;

use Avax\Components\Security\Secrets\System\Capabilities\Stores\InMemorySecretStore;
use Avax\Components\Security\Secrets\System\Capabilities\Stores\SecretStore;

final class Secrets
{
    private static SecretStore $store;

    public static function has(string $key): bool
    {
        return self::store()->has($key);
    }

    private static function store(): SecretStore
    {
        if (! isset(self::$store)) {
            self::$store = new InMemorySecretStore;
        }

        return self::$store;
    }

    public static function forget(string $key): void
    {
        self::store()->forget($key);
    }

    public static function redact(string $key): string
    {
        $value = self::get($key);

        if (strlen($value) <= 4) {
            return '****';
        }

        return substr($value, 0, 2).str_repeat('*', strlen($value) - 4).substr($value, -2);
    }

    public static function get(string $key): string
    {
        return self::store()->get($key);
    }

    public static function rotate(string $key, string $newValue): void
    {
        self::store()->set($key, $newValue);
    }

    public static function set(string $key, string $value): void
    {
        self::store()->set($key, $value);
    }
}
