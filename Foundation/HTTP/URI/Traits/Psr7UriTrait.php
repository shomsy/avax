<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Traits;

use Avax\HTTP\URI\Components\Host;
use Avax\HTTP\URI\Components\Path;
use Avax\HTTP\URI\Components\Scheme;
use Avax\HTTP\URI\QueryParams;
use Psr\Http\Message\UriInterface;
use SensitiveParameter;

trait Psr7UriTrait
{
    public function getScheme() : string
    {
        return $this->scheme;
    }

    public function getUserInfo() : string
    {
        return sprintf("%s%s", $this->user, $this->password !== null ? ':' . $this->password : '');
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function getPort() : int|null
    {
        return $this->port;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getQuery() : string
    {
        return $this->queryParams->toString();
    }

    public function getFragment() : string
    {
        return $this->fragment;
    }

    public function withUserInfo(string $user, #[SensitiveParameter] string|null $password = null) : UriInterface
    {
        return new self(
            scheme  : $this->scheme,
            host    : $this->host,
            path    : $this->path,
            port    : $this->port,
            query   : $this->queryParams->toString(),
            fragment: $this->fragment,
            user    : $user,
            password: $password
        );
    }

    public function withPort(int|null $port) : UriInterface
    {
        return new self(
            scheme  : $this->scheme,
            host    : $this->host,
            path    : $this->path,
            port    : $this->validatePort(port: $port, scheme: $this->scheme),
            query   : $this->queryParams->toString(),
            fragment: $this->fragment,
            user    : $this->user,
            password: $this->password
        );
    }

    public function withQuery(string $query) : UriInterface
    {
        $clone              = clone $this;
        $clone->queryParams = new QueryParams(queryString: $this->validateQuery(query: $query));

        return $clone;
    }

    public function withFragment(string $fragment) : UriInterface
    {
        return new self(
            scheme  : $this->scheme,
            host    : $this->host,
            path    : $this->path,
            port    : $this->port,
            query   : $this->queryParams->toString(),
            fragment: $this->validateFragment(fragment: $fragment),
            user    : $this->user,
            password: $this->password
        );
    }

    public function withPath(string $path) : self
    {
        return new self(
            scheme  : $this->scheme,
            host    : $this->host,
            path    : (string) (new Path(path: $path)),
            port    : $this->port,
            query   : $this->queryParams->toString(),
            fragment: $this->fragment,
            user    : $this->user,
            password: $this->password
        );
    }

    public function withHost(string $host) : self
    {
        return new self(
            scheme  : $this->scheme,
            host    : (string) (new Host(host: $host)),
            path    : $this->path,
            port    : $this->port,
            query   : $this->queryParams->toString(),
            fragment: $this->fragment,
            user    : $this->user,
            password: $this->password
        );
    }

    public function withScheme(string $scheme) : self
    {
        return new self(
            scheme  : (string) (new Scheme(scheme: $scheme)),
            host    : $this->host,
            path    : $this->path,
            port    : $this->port,
            query   : $this->queryParams->toString(),
            fragment: $this->fragment,
            user    : $this->user,
            password: $this->password
        );
    }

    public function getAuthority() : string
    {
        $authority = $this->host;
        if ($this->user) {
            $authority = $this->user . ($this->password ? ':' . $this->password : '') . ('@' . $authority);
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }


}
