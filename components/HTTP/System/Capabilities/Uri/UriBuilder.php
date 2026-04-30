<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Uri;

use InvalidArgumentException;
use Stringable;

/**
 * Fluent URI builder for programmatic URL construction.
 */
final class UriBuilder implements Stringable
{
    private string  $scheme      = '';
    private string  $host        = '';
    private ?int    $port        = null;
    private string  $path        = '';
    private array   $queryParams = [];
    private string  $fragment    = '';
    private string  $user        = '';
    private ?string $password    = null;

    public static function createFromString(string $uri) : self
    {
        $parts = parse_url($uri);

        if ($parts === false) {
            throw new InvalidArgumentException("Invalid URI: {$uri}");
        }

        $builder           = new self();
        $builder->scheme = $parts['scheme'] ?? '';
        $builder->host = $parts['host'] ?? '';
        $builder->path = $parts['path'] ?? '';
        $builder->port = $parts['port'] ?? null;
        $builder->fragment = $parts['fragment'] ?? '';
        $builder->user = $parts['user'] ?? '';
        $builder->password = $parts['pass'] ?? null;

        if (isset($parts['query'])) {
            parse_str($parts['query'], $builder->queryParams);
        }

        return $builder;
    }

    public function withScheme(string $scheme) : self
    {
        $clone         = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    public function withHost(string $host) : self
    {
        $clone       = clone $this;
        $clone->host = $host;

        return $clone;
    }

    public function withPort(?int $port) : self
    {
        $clone       = clone $this;
        $clone->port = $port;

        return $clone;
    }

    public function withPath(string $path) : self
    {
        $clone       = clone $this;
        $clone->path = $path;

        return $clone;
    }

    public function appendPath(string $segment) : self
    {
        $clone       = clone $this;
        $clone->path = rtrim($this->path, '/') . '/' . ltrim($segment, '/');

        return $clone;
    }

    public function withQueryParam(string $key, mixed $value) : self
    {
        $clone                    = clone $this;
        $clone->queryParams[$key] = $value;

        return $clone;
    }

    public function withQueryParams(array $params) : self
    {
        $clone              = clone $this;
        $clone->queryParams = array_merge($clone->queryParams, $params);

        return $clone;
    }

    public function withoutQueryParam(string $key) : self
    {
        $clone = clone $this;
        unset($clone->queryParams[$key]);

        return $clone;
    }

    public function withFragment(string $fragment) : self
    {
        $clone           = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    public function withUserInfo(string $user, string $password = null) : self
    {
        $clone           = clone $this;
        $clone->user     = $user;
        $clone->password = $password;

        return $clone;
    }

    public function toUri() : Uri
    {
        return new Uri(
            scheme  : $this->scheme,
            host    : $this->host,
            path    : '/' . ltrim($this->path, '/'),
            port    : $this->port,
            query   : $this->queryParams !== [] ? http_build_query($this->queryParams) : '',
            fragment: $this->fragment,
            user    : $this->user,
            password: $this->password,
        );
    }

    public function __toString() : string
    {
        return $this->build();
    }

    public function build() : string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . '://';
        }

        if ($this->user !== '') {
            $uri .= $this->user;
            if ($this->password !== null) {
                $uri .= ':' . $this->password;
            }
            $uri .= '@';
        }

        $uri .= $this->host;

        if ($this->port !== null && ! $this->isDefaultPort()) {
            $uri .= ':' . $this->port;
        }

        $uri .= '/' . ltrim($this->path, '/');

        if ($this->queryParams !== []) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    private function isDefaultPort() : bool
    {
        return ($this->scheme === 'http' && $this->port === 80)
            || ($this->scheme === 'https' && $this->port === 443);
    }
}
