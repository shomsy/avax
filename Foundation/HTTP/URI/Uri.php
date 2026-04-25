<?php

declare(strict_types=1);

namespace Avax\HTTP\URI;

use Avax\HTTP\URI\Parts\Authority;
use Avax\HTTP\URI\Parts\Fragment;
use Avax\HTTP\URI\Parts\Host;
use Avax\HTTP\URI\Parts\Path;
use Avax\HTTP\URI\Parts\Port;
use Avax\HTTP\URI\Parts\Query;
use Avax\HTTP\URI\Parts\Scheme;
use Avax\HTTP\URI\Parts\UserInfo;
use Psr\Http\Message\UriInterface;
use SensitiveParameter;
use Stringable;

/**
 * Immutable URI model.
 */
final readonly class Uri implements UriInterface, Stringable
{
    private Scheme|null    $scheme;
    private Authority|null $authority;
    private Path           $path;
    private Query          $query;
    private Fragment|null  $fragment;

    public function __construct(
        Scheme|null    $scheme = null,
        Authority|null $authority = null,
        Path           $path,
        Query          $query,
        Fragment|null  $fragment = null
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
        $parts = ParseUriString::parse(uri: $uri);

        return new self(
            scheme   : $parts['scheme'],
            authority: $parts['authority'],
            path     : $parts['path'],
            query    : $parts['query'],
            fragment : $parts['fragment']
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

    public function getPort() : int|null
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
        $newScheme = $scheme !== '' ? new Scheme(scheme: $scheme) : null;

        return new self(scheme: $newScheme, authority: $this->authority, path: $this->path, query: $this->query, fragment: $this->fragment);
    }

    public function withUserInfo(string $user, #[SensitiveParameter] string|null $password = null) : UriInterface
    {
        $userInfo  = $user !== '' ? new UserInfo(user: $user, password: $password) : null;
        $authority = $this->authority ? new Authority(host: $this->authority->host(), port: $this->authority->port(), userInfo: $userInfo) : null;

        return new self(scheme: $this->scheme, authority: $authority, path: $this->path, query: $this->query, fragment: $this->fragment);
    }

    public function withHost(string $host) : UriInterface
    {
        $newHost   = new Host(host: $host);
        $authority = new Authority(host: $newHost, port: $this->authority ? $this->authority->port() : null, userInfo: $this->authority ? $this->authority->userInfo() : null);

        return new self(scheme: $this->scheme, authority: $authority, path: $this->path, query: $this->query, fragment: $this->fragment);
    }

    public function withPort(int|null $port) : UriInterface
    {
        $newPort   = $port !== null && $this->scheme ? new Port(port: $port, scheme: $this->scheme) : null;
        $authority = $this->authority ? new Authority(host: $this->authority->host(), port: $newPort, userInfo: $this->authority->userInfo()) : null;

        return new self(scheme: $this->scheme, authority: $authority, path: $this->path, query: $this->query, fragment: $this->fragment);
    }

    public function withPath(string $path) : UriInterface
    {
        return new self(scheme: $this->scheme, authority: $this->authority, path: new Path(path: $path), query: $this->query, fragment: $this->fragment);
    }

    public function withQuery(string $query) : UriInterface
    {
        return new self(scheme: $this->scheme, authority: $this->authority, path: $this->path, query: new Query(queryString: $query), fragment: $this->fragment);
    }

    public function withFragment(string $fragment) : UriInterface
    {
        $newFragment = $fragment !== '' ? new Fragment(fragment: $fragment) : null;

        return new self(scheme: $this->scheme, authority: $this->authority, path: $this->path, query: $this->query, fragment: $newFragment);
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