<?php

declare(strict_types=1);

namespace Avax\Labs\Integration\ObjectStorage\System\Capabilities\Ports;

class ObjectStorageResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $error = null,
        public readonly ?string $url = null,
    ) {
    }

    public static function success(?string $url = null): self
    {
        return new self(true, null, $url);
    }

    public static function failure(string $error): self
    {
        return new self(false, $error);
    }
}
