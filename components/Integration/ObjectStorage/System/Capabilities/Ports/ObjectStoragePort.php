<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports;

interface ObjectStoragePort
{
    /**
     * @param array<string, mixed> $options
     */
    public function store(string $key, string $content, array $options = []) : ObjectStorageResult;

    public function read(string $key) : string|null;

    public function delete(string $key) : bool;

    public function exists(string $key) : bool;

    public function generatePresignedUrl(string $key, int $expiresInSeconds) : string;
}
