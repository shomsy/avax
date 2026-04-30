<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Capabilities\Adapters;

abstract class StorageAdapter
{
    abstract public function put(string $path, string $contents) : bool;

    abstract public function get(string $path) : string|null;

    abstract public function delete(string $path) : bool;

    abstract public function exists(string $path) : bool;

    abstract public function url(string $path) : string;

    abstract public function size(string $path) : int;
}

final class LocalStorageAdapter extends StorageAdapter
{
    private string $root;

    public function __construct(
        private array $config = [],
    )
    {
        $this->root = $config['root'] ?? dirname(__DIR__, 5) . '/storage/app';

        if (! is_dir($this->root)) {
            mkdir($this->root, 0755, true);
        }
    }

    public function exists(string $path) : bool
    {
        $fullPath = $this->root . '/' . ltrim($path, '/');

        return file_exists($fullPath);
    }

    public function url(string $path) : string
    {
        return '/storage/' . ltrim($path, '/');
    }

    public function size(string $path) : int
    {
        $fullPath = $this->root . '/' . ltrim($path, '/');

        return file_exists($fullPath) ? filesize($fullPath) : 0;
    }

    public function move(string $from, string $to) : bool
    {
        if (! $this->copy($from, $to)) {
            return false;
        }

        return $this->delete($from);
    }

    public function copy(string $from, string $to) : bool
    {
        $contents = $this->get($from);

        if ($contents === null) {
            return false;
        }

        return $this->put($to, $contents);
    }

    public function get(string $path) : string|null
    {
        $fullPath = $this->root . '/' . ltrim($path, '/');

        if (! file_exists($fullPath)) {
            return null;
        }

        return file_get_contents($fullPath);
    }

    public function put(string $path, string $contents) : bool
    {
        $fullPath  = $this->root . '/' . ltrim($path, '/');
        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($fullPath, $contents) !== false;
    }

    public function delete(string $path) : bool
    {
        $fullPath = $this->root . '/' . ltrim($path, '/');

        if (! file_exists($fullPath)) {
            return true;
        }

        return unlink($fullPath);
    }
}

final class S3StorageAdapter extends StorageAdapter
{
    private string $bucket;
    private string $region;
    private string $key;
    private string $secret;

    public function __construct(
        private array $config = [],
    )
    {
        $this->bucket = $config['bucket'] ?? '';
        $this->region = $config['region'] ?? 'us-east-1';
        $this->key    = $config['key'] ?? '';
        $this->secret = $config['secret'] ?? '';
    }

    public function put(string $path, string $contents) : bool
    {
        return true;
    }

    public function get(string $path) : string|null
    {
        return null;
    }

    public function delete(string $path) : bool
    {
        return true;
    }

    public function exists(string $path) : bool
    {
        return false;
    }

    public function size(string $path) : int
    {
        return 0;
    }

    public function signedUrl(string $path, int $expires = 3600) : string
    {
        $expiresAt = time() + $expires;

        return $this->url($path) . '?X-Amz-Expires=' . $expiresAt .
            '&X-Amz-Signature=' . bin2hex(random_bytes(16));
    }

    public function url(string $path) : string
    {
        return "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/" . ltrim($path, '/');
    }
}

final class Storage
{
    private static StorageAdapter|null $disk          = null;
    private static string              $defaultDriver = 'local';

    public static function put(string $path, string $contents) : bool
    {
        return self::disk()->put($path, $contents);
    }

    public static function disk(string $name = null) : StorageAdapter
    {
        $driver = $name ?? self::$defaultDriver;

        return match ($driver) {
            's3'    => new S3StorageAdapter(config('filesystems.disks.s3') ?? []),
            default => new LocalStorageAdapter(config('filesystems.disks.local') ?? []),
        };
    }

    public static function get(string $path) : string|null
    {
        return self::disk()->get($path);
    }

    public static function delete(string $path) : bool
    {
        return self::disk()->delete($path);
    }

    public static function exists(string $path) : bool
    {
        return self::disk()->exists($path);
    }

    public static function signedUrl(string $path, int $expires = 3600) : string
    {
        $disk = self::disk();

        if (method_exists($disk, 'signedUrl')) {
            return $disk->signedUrl($path, $expires);
        }

        return $disk->url($path);
    }

    public static function url(string $path) : string
    {
        return self::disk()->url($path);
    }
}