<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Uri;

use Psr\Http\Message\UriInterface;

final class RequestUri implements UriInterface
{
    public function __construct(
        private string $scheme = '',
        private string $userInfo = '',
        private string $host = '',
        private ?int $port = null,
        private string $path = '',
        private string $query = '',
        private string $fragment = '',
    ) {}

    public function getScheme() : string
    {
        return $this->scheme;
    }

    public function getAuthority() : string
    {
        return $this->host;
    }

    public function getUserInfo() : string
    {
        return $this->userInfo;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function getPort() : ?int
    {
        return $this->port;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getQuery() : string
    {
        return $this->query;
    }

    public function getFragment() : string
    {
        return $this->fragment;
    }

    public function withScheme($scheme) : self
    {
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    public function withUserInfo($userInfo, $password = null) : self
    {
        $clone = clone $this;
        $clone->userInfo = $userInfo;

        return $clone;
    }

    public function withHost($host) : self
    {
        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    public function withPort($port) : self
    {
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    public function withPath($path) : self
    {
        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    public function withQuery($query) : self
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    public function withFragment($fragment) : self
    {
        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    public function __toString() : string
    {
        return ($this->scheme !== '' && $this->scheme !== '0' ? $this->scheme . '://' : '') . $this->host . $this->path . ($this->query !== '' && $this->query !== '0' ? '?' . $this->query : '');
    }
}
