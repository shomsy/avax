<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

final readonly class Request
{
    /**
     * @param  array<string, mixed>  $server
     * @param  array<string, mixed>  $get
     * @param  array<string, mixed>  $post
     * @param  array<string, mixed>  $files
     */
    public function __construct(
        public array $server,
        public array $get,
        public array $post,
        public array $files,
    ) {
    }

    public function method(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_'.str_replace('-', '_', strtoupper($name));

        return $this->server[$key] ?? null;
    }
}
