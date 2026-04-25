<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

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
