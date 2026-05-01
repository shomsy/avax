<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Capabilities\Adapters;

interface StorageAdapter
{
    public function put(string $path, string $contents): bool;

    public function get(string $path): ?string;

    public function delete(string $path): bool;

    public function exists(string $path): bool;

    public function url(string $path): string;

    public function signedUrl(string $path, int $expiresInSeconds = 3600): string;

    public function size(string $path): int;

    /**
     * @return list<string>
     */
    public function files(string $directory = ''): array;

    public function makeDirectory(string $directory): bool;

    public function deleteDirectory(string $directory): bool;
}
