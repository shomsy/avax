<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Capabilities\Adapters;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final readonly class LocalStorageAdapter implements StorageAdapter
{
    private string $root;

    public function __construct(private array $config = [])
    {
        $this->root = rtrim(
            string    : $config['root'] ?? dirname(path: __DIR__, levels: 6).'/storage/app',
            characters: '/',
        );

        $this->makeDirectory(directory: '');
    }

    public function makeDirectory(string $directory): bool
    {
        $fullPath = $directory === '' || $directory === '.' ? $this->root : $this->fullPath(path: $directory);

        if (is_dir(filename: $fullPath)) {
            return true;
        }

        return mkdir(directory: $fullPath, permissions: 0o755, recursive: true);
    }

    private function fullPath(string $path): string
    {
        return $this->root.'/'.$this->normalize(path: $path);
    }

    private function normalize(string $path): string
    {
        $normalized = trim(string: str_replace(search: '\\', replace: '/', subject: $path), characters: '/');

        if (str_contains(haystack: $normalized, needle: '..')) {
            throw new RuntimeException(message: 'Storage paths must stay inside the configured disk root.');
        }

        return $normalized;
    }

    public function exists(string $path): bool
    {
        return file_exists(filename: $this->fullPath(path: $path));
    }

    public function signedUrl(string $path, int $expiresInSeconds = 3600): string
    {
        $expiresAt = time() + $expiresInSeconds;
        $normalizedPath = $this->normalize(path: $path);
        $secret = $this->config['signing_key'] ?? 'avax-local-storage';
        $signature = hash_hmac(algo: 'sha256', data: $normalizedPath.'|'.$expiresAt, key: $secret);

        return $this->url(path: $normalizedPath).'?expires='.$expiresAt.'&signature='.$signature;
    }

    public function url(string $path): string
    {
        return rtrim(string: $this->config['url'] ?? '/storage', characters: '/').'/'.$this->normalize(path: $path);
    }

    public function size(string $path): int
    {
        $fullPath = $this->fullPath(path: $path);

        return is_file(filename: $fullPath) ? (int) filesize(filename: $fullPath) : 0;
    }

    public function move(string $from, string $to): bool
    {
        return $this->copy(from: $from, to: $to) && $this->delete(path: $from);
    }

    public function copy(string $from, string $to): bool
    {
        $contents = $this->get(path: $from);

        return $contents !== null && $this->put(path: $to, contents: $contents);
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->fullPath(path: $path);

        if (! is_file(filename: $fullPath)) {
            return null;
        }

        $contents = file_get_contents(filename: $fullPath);

        return $contents === false ? null : $contents;
    }

    public function put(string $path, string $contents): bool
    {
        $fullPath = $this->fullPath(path: $path);
        $this->makeDirectory(directory: dirname(path: $path));

        return file_put_contents(filename: $fullPath, data: $contents) !== false;
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->fullPath(path: $path);

        if (! file_exists(filename: $fullPath)) {
            return true;
        }

        return unlink(filename: $fullPath);
    }

    public function files(string $directory = ''): array
    {
        $root = $this->fullPath(path: $directory);

        if (! is_dir(filename: $root)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(directory: $root, flags: RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = ltrim(
                    string    : str_replace(search: $this->root, replace: '', subject: $file->getPathname()),
                    characters: '/',
                );
            }
        }

        sort(array: $files);

        return $files;
    }

    public function deleteDirectory(string $directory): bool
    {
        $fullPath = $this->fullPath(path: $directory);

        if (! is_dir(filename: $fullPath)) {
            return true;
        }

        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(directory: $fullPath, flags: RecursiveDirectoryIterator::SKIP_DOTS),
            mode    : RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir(directory: $file->getPathname());
            } else {
                unlink(filename: $file->getPathname());
            }
        }

        return rmdir(directory: $fullPath);
    }
}
