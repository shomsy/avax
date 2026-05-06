<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Capabilities\Adapters;

use Avax\Components\Operations\Filesystem\System\Capabilities\Drivers\Local;

final readonly class LocalStorageAdapter
{
    private Local $local;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->local = new Local(config: $config);
    }

    public function put(string $path, string $contents): bool
    {
        return $this->local->put(path: $path, contents: $contents);
    }

    public function get(string $path): string|null
    {
        return $this->local->get(path: $path);
    }

    public function delete(string $path): bool
    {
        return $this->local->delete(path: $path);
    }

    public function exists(string $path): bool
    {
        return $this->local->exists(path: $path);
    }

    public function url(string $path): string
    {
        return $this->local->url(path: $path);
    }

    public function signedUrl(string $path, int $expiresInSeconds = 3600): string
    {
        return $this->local->signedUrl(path: $path, expiresInSeconds: $expiresInSeconds);
    }

    public function size(string $path): int
    {
        return $this->local->size(path: $path);
    }

    /**
     * @return list<string>
     */
    public function files(string $directory = ''): array
    {
        return $this->local->files(directory: $directory);
    }

    public function makeDirectory(string $directory): bool
    {
        return $this->local->makeDirectory(directory: $directory);
    }

    public function deleteDirectory(string $directory): bool
    {
        return $this->local->deleteDirectory(directory: $directory);
    }
}
