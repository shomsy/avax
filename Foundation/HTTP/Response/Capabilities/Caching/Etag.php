<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Caching;

final readonly class Etag
{
    public function __construct(
        public string $value,
        public bool   $weak = false,
    ) {}

    public function toHeaderValue() : string
    {
        $quoted = '"' . trim(string: $this->value, characters: '"') . '"';

        return $this->weak ? "W/{$quoted}" : $quoted;
    }
}
