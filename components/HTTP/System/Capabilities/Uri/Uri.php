<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Uri;

final class Uri
{
    public function __construct(
        private string $scheme = 'http',
        private string $host = 'localhost',
        private int $port = 80,
        private string $path = '/',
        private string $query = '',
    ) {}

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function toString(): string
    {
        $url = "{$this->scheme}://{$this->host}";
        
        if ($this->port && !in_array($this->port, [80, 443])) {
            $url .= ":{$this->port}";
        }
        
        $url .= $this->path;
        
        if ($this->query) {
            $url .= "?{$this->query}";
        }
        
        return $url;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}