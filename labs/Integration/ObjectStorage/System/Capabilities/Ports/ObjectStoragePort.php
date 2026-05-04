<?php

declare(strict_types=1);

namespace Avax\Labs\Integration\ObjectStorage\System\Capabilities\Ports;

interface ObjectStoragePort
{
    public function store(string $key, string $content, array $options = []): ObjectStorageResult;

    public function read(string $key): ?string;

    public function delete(string $key): bool;

    public function exists(string $key): bool;

    public function generatePresignedUrl(string $key, int $expiresInSeconds): string;
}
