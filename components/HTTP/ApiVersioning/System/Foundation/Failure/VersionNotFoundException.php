<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\Foundation\Failure;

use RuntimeException;

final class VersionNotFoundException extends RuntimeException
{
    public function __construct(string $version, ?string $message = null)
    {
        $message ??= "API version not found: {$version}";
        parent::__construct($message, 400);
    }
}
