<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

final class Request
{
    public function __construct(
        public readonly array $server,
        public readonly array $get,
        public readonly array $post,
        public readonly array $files,
    ) {}

    public function method() : string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function uri() : string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function header(string $name) : ?string
    {
        $key = 'HTTP_' . str_replace('-', '_', strtoupper($name));

        return $this->server[$key] ?? null;
    }
}
