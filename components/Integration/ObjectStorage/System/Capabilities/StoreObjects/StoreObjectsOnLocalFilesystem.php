<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Capabilities\StoreObjects;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult;

class StoreObjectsOnLocalFilesystem implements ObjectStoragePort
{
    private string $basePath;

    public function __construct(string $basePath = '/tmp/avax-object-storage')
    {
        $this->basePath = $basePath;
        if (! is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function store(string $key, string $content, array $options = []) : ObjectStorageResult
    {
        $path = $this->basePath . '/' . $key;
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $result = file_put_contents($path, $content);

        if ($result === false) {
            return ObjectStorageResult::failure('Failed to write object');
        }

        return ObjectStorageResult::success();
    }

    public function read(string $key) : ?string
    {
        $path = $this->basePath . '/' . $key;

        if (! file_exists($path)) {
            return null;
        }

        return file_get_contents($path) ?: null;
    }

    public function delete(string $key) : bool
    {
        $path = $this->basePath . '/' . $key;

        if (! file_exists($path)) {
            return false;
        }

        return unlink($path);
    }

    public function exists(string $key) : bool
    {
        return file_exists($this->basePath . '/' . $key);
    }

    public function generatePresignedUrl(string $key, int $expiresInSeconds) : string
    {
        return "file://{$this->basePath}/{$key}?expires={$expiresInSeconds}";
    }
}
