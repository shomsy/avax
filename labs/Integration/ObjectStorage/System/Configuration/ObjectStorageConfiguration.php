<?php

declare(strict_types=1);

namespace Avax\Labs\Integration\ObjectStorage\System\Configuration;

interface ObjectStorageConfiguration
{
    public function getBucket(): string;

    public function getRegion(): string;

    public function getEndpoint(): ?string;

    public function getAccessKey(): ?string;

    public function getSecretKey(): ?string;

    public function isPublic(): bool;
}