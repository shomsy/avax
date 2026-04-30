<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Capabilities\Adapters;

final class Storage
{
    /** @var array<string, StorageAdapter> */
    private static array $disks = [];

    public static function useDisk(string $name, StorageAdapter $adapter) : void
    {
        self::$disks[$name] = $adapter;
    }

    public static function put(string $path, string $contents) : bool
    {
        return self::disk()->put(path: $path, contents: $contents);
    }

    public static function disk(string|null $name = null) : StorageAdapter
    {
        $disk = $name ?? 'local';

        if (! isset(self::$disks[$disk])) {
            self::$disks[$disk] = self::buildDisk(name: $disk);
        }

        return self::$disks[$disk];
    }

    private static function buildDisk(string $name) : StorageAdapter
    {
        $config = function_exists(function: 'config') ? (config(key: "filesystems.disks.{$name}", default: []) ?? []) : [];

        return match ($name) {
            's3'    => new S3StorageAdapter(config: $config),
            default => new LocalStorageAdapter(config: $config),
        };
    }

    public static function get(string $path) : string|null
    {
        return self::disk()->get(path: $path);
    }

    public static function delete(string $path) : bool
    {
        return self::disk()->delete(path: $path);
    }

    public static function exists(string $path) : bool
    {
        return self::disk()->exists(path: $path);
    }

    public static function url(string $path) : string
    {
        return self::disk()->url(path: $path);
    }

    public static function signedUrl(string $path, int $expiresInSeconds = 3600) : string
    {
        return self::disk()->signedUrl(path: $path, expiresInSeconds: $expiresInSeconds);
    }
}
