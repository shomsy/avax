<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\PublicSurface;

final class ResponseFormatter
{
    /** @var array<string, string> */
    private array $headers = [];

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
