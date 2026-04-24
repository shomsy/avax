<?php

declare(strict_types=1);

namespace Avax\HTTP\URI;

use Avax\HTTP\URI\Parts\Authority;
use Avax\HTTP\URI\Parts\Fragment;
use Avax\HTTP\URI\Parts\Path;
use Avax\HTTP\URI\Parts\Query;
use Avax\HTTP\URI\Parts\Scheme;
use Psr\Http\Message\UriInterface;
use Stringable;

/**
 * Immutable URI model.
 */
final readonly class Uri implements UriInterface, Stringable
{
    private ?Scheme    $scheme;
    private ?Authority $authority;
    private Path       $path;
    private Query      $query;
    private ?Fragment  $fragment;

    public function __construct(
        ?Scheme    $scheme = null,
        ?Authority $authority = null,
        Path       $path,
        Query      $query,
        ?Fragment  $fragment = null
    )
    {
        $this->scheme    = $scheme;
        $this->authority = $authority;
        $this->path      = $path;
        $this->query     = $query;
        $this->fragment  = $fragment;
    }

    public static function fromString(string $uri) : self
    {
        $parts = ParseUriString::parse($uri);

        return new self(
            $parts['scheme'],
            $parts['authority'],
            $parts['path'],
            $parts['query'],
            $parts['fragment']
        );
    }

    public function getScheme() : string
    {
        return $this->scheme ? (string) $this->scheme : '';
    }

    public function getAuthority() : string
    {
        return $this->authority ? (string) $this->authority : '';
    }

    public function getUserInfo() : string
    {
        return $this->authority && $this->authority->userInfo() ? (string) $this->authority->userInfo() : '';
    }

    public function getHost() : string
    {
        return $this->authority ? (string) $this->authority->host() : '';
    }

    public function getPort() : ?int
    {
        return $this->authority && $this->authority->port() ? $this->authority->port()->value() : null;
    }

    public function getPath() : string
    {
        return (string) $this->path;
    }

    public function getQuery() : string
    {
        return (string) $this->query;
    }

    public function getFragment() : string
    {
        return $this->fragment ? (string) $this->fragment : '';
    }

    public function withScheme(string $scheme) : UriInterface
    {
        $newScheme = $scheme !== '' ? new Scheme($scheme) : null;

        return new self($newScheme, $this->authority, $this->path, $this->query, $this->fragment);
    }

    public function withUserInfo(string $user, ?string $password = null) : UriInterface
    {
        $userInfo  = $user !== '' ? new \Avax\HTTP\URI\Parts\UserInfo($user, $password) : null;
        $authority = $this->authority ? new Authority($this->authority->host(), $this->authority->port(), $userInfo) : null;

        return new self($this->scheme, $authority, $this->path, $this->query, $this->fragment);
    }

    public function withHost(string $host) : UriInterface
    {
        $newHost   = new \Avax\HTTP\URI\Parts\Host($host);
        $authority = new Authority($newHost, $this->authority ? $this->authority->port() : null, $this->authority ? $this->authority->userInfo() : null);

        return new self($this->scheme, $authority, $this->path, $this->query, $this->fragment);
    }

    public function withPort(?int $port) : UriInterface
    {
        $newPort   = $port !== null && $this->scheme ? new \Avax\HTTP\URI\Parts\Port($port, $this->scheme) : null;
        $authority = $this->authority ? new Authority($this->authority->host(), $newPort, $this->authority->userInfo()) : null;

        return new self($this->scheme, $authority, $this->path, $this->query, $this->fragment);
    }

    public function withPath(string $path) : UriInterface
    {
        return new self($this->scheme, $this->authority, new Path($path), $this->query, $this->fragment);
    }

    public function withQuery(string $query) : UriInterface
    {
        return new self($this->scheme, $this->authority, $this->path, new Query($query), $this->fragment);
    }

    public function withFragment(string $fragment) : UriInterface
    {
        $newFragment = $fragment !== '' ? new Fragment($fragment) : null;

        return new self($this->scheme, $this->authority, $this->path, $this->query, $newFragment);
    }

    public function __toString() : string
    {
        $uri = '';
        if ($this->scheme) {
            $uri .= $this->scheme . '://';
        }
        if ($this->authority) {
            $uri .= $this->authority;
        }
        $uri .= $this->path;
        if ((string) $this->query !== '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}