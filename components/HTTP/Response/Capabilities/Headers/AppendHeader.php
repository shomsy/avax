<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Headers;

use SensitiveParameter;

final class AppendHeader
{
    public function __invoke(#[SensitiveParameter] ResponseHeaders $headers, string $name, mixed $value) : ResponseHeaders
    {
        return $headers->append(name: $name, value: $value);
    }
}
