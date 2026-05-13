<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports;

class ObjectStorageResult
{
    public function __construct(
        public readonly bool    $success,
        public readonly ?string $error = null,
        public readonly ?string $url = null,
        public readonly ?string $content = null,
    ) {}

    public static function success(string|null $url = null, ?string $content = null) : self
    {
        return new self(true, null, $url, $content);
    }

    public static function failure(string $error) : self
    {
        return new self(false, $error);
    }
}
