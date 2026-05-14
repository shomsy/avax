<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Capabilities\StoreObjects;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult;

class StoreObjectsInMemory implements ObjectStoragePort
{
    /**
     * @var array<string, string>
     */
    private array $storage = [];

    /**
     * @param array<string, mixed> $options
     */
    public function store(string $key, string $content, array $options = []) : ObjectStorageResult
    {
        $this->storage[$key] = $content;

        return ObjectStorageResult::success();
    }

    public function read(string $key) : string|null
    {
        return $this->storage[$key] ?? null;
    }

    public function delete(string $key) : bool
    {
        unset($this->storage[$key]);

        return true;
    }

    public function exists(string $key) : bool
    {
        return isset($this->storage[$key]);
    }

    public function generatePresignedUrl(string $key, int $expiresInSeconds) : string
    {
        return "/storage/fake/{$key}?expires={$expiresInSeconds}";
    }

    /**
     * @return array<string, string>
     */
    public function all() : array
    {
        return $this->storage;
    }

    public function clear() : void
    {
        $this->storage = [];
    }
}
