<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Headers;

use SensitiveParameter;

final class ReadHeader
{
    /**
     * @return list<string>
     */
    public function __invoke(#[SensitiveParameter] ResponseHeaders $headers, string $name) : array
    {
        return $headers->read(name: $name);
    }
}
