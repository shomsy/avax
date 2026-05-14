<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Capabilities\StoreObjects;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult;

class StoreObjectsOnLocalFilesystem implements ObjectStoragePort
{
    private Filesystem $filesystem;

    private string $basePath;

    public function __construct(string $basePath, Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->basePath = $basePath;
        if (! $this->filesystem->exists($this->basePath)) {
            $this->filesystem->createDirectory($this->basePath, 0o755);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function store(string $key, string $content, array $options = []) : ObjectStorageResult
    {
        $path = $this->basePath . '/' . $key;
        $dir  = dirname($path);

        if (! $this->filesystem->exists($dir)) {
            $this->filesystem->createDirectory($dir, 0o755);
        }

        $result = $this->filesystem->write($path, $content);

        if (! $result) {
            return ObjectStorageResult::failure('Failed to write object');
        }

        return ObjectStorageResult::success();
    }

    public function read(string $key) : string|null
    {
        $path = $this->basePath . '/' . $key;

        if (! $this->filesystem->exists($path)) {
            return null;
        }

        return $this->filesystem->read($path) ?: null;
    }

    public function delete(string $key) : bool
    {
        $path = $this->basePath . '/' . $key;

        if (! $this->filesystem->exists($path)) {
            return false;
        }

        return $this->filesystem->delete($path);
    }

    public function exists(string $key) : bool
    {
        return $this->filesystem->exists($this->basePath . '/' . $key);
    }

    public function generatePresignedUrl(string $key, int $expiresInSeconds) : string
    {
        return "file://{$this->basePath}/{$key}?expires={$expiresInSeconds}";
    }
}
