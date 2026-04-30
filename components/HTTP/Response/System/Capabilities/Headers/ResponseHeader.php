<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities\Headers;

final class ResponseHeader
{
    public function __construct(
        private string $name,
        private array $values,
    ) {}

    public function name() : string
    {
        return $this->name;
    }

    public function values() : array
    {
        return $this->values;
    }
}
