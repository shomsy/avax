<?php

declare(strict_types=1);

namespace Avax\HTTP\URI;

use Avax\HTTP\URI\Components\Host;
use Avax\HTTP\URI\Components\Path;
use Avax\HTTP\URI\Components\Scheme;
use Avax\HTTP\URI\Traits\Psr7UriTrait;
use Avax\HTTP\URI\Traits\QueryParameterTrait;
use Avax\HTTP\URI\Traits\UriValidationTrait;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

final class UriBuilder extends BaseUri implements UriInterface
{
    use QueryParameterTrait;
    use Psr7UriTrait;
    use UriValidationTrait;

    private QueryParams $queryParams;

    public function __construct(
        string      $scheme = '',
        string      $host = '',
        string      $path = '/',
        int|null    $port = null,
        string      $query = '',
        string      $fragment = '',
        string      $user = '',
        string|null $password = null
    ) {
        parent::__construct(
            scheme  : (string) new Scheme(scheme: $scheme),
            host    : (string) new Host(host: $host),
            path    : (string) new Path(path: $path),
            port    : $port,
            query   : '',
            fragment: $fragment,
            user    : $user,
            password: $password
        );

        $this->queryParams = new QueryParams(queryString: $query);
    }

    public static function fromBaseUri(string $baseUri, array $overrides = []) : UriInterface
    {
        $instance = self::createFromString(uri: $baseUri);

        return $instance
            ->withScheme(scheme: $overrides['scheme'] ?? $instance->getScheme())
            ->withHost(host: $overrides['host'] ?? $instance->getHost())
            ->withPath(path: $overrides['path'] ?? $instance->getPath())
            ->withPort(port: $overrides['port'] ?? $instance->getPort())
            ->withQuery(query: $overrides['query'] ?? $instance->getQuery())
            ->withFragment(fragment: $overrides['fragment'] ?? $instance->getFragment());
    }

    public static function createFromString(string $uri) : self
    {
        $parts = parse_url($uri);

        if ($parts === false) {
            throw new InvalidArgumentException(message: "Invalid URI: {$uri}");
        }

        return new self(
            scheme  : $parts['scheme'] ?? '',
            host    : $parts['host'] ?? '',
            path    : $parts['path'] ?? '/',
            port    : $parts['port'] ?? null,
            query   : $parts['query'] ?? '',
            fragment: $parts['fragment'] ?? '',
            user    : $parts['user'] ?? '',
            password: $parts['pass'] ?? null
        );
    }

    public function appendPath(string $segment) : self
    {
        [$path, $query] = explode('?', $segment, 2) + [1 => ''];

        $newPath = rtrim($this->path, '/') . '/' . ltrim($path, '/');
        $clone   = $this->withPath($newPath);

        if ($query !== '') {
            parse_str($query, $queryParams);
            $clone = $clone->withAddedQueryParams($queryParams);
        }

        return $clone;
    }

    public function withAddedQueryParams(array $params) : self
    {
        return $this->addParams(params: $params);
    }

    public function build() : string
    {
        $uri = $this->scheme ? "{$this->scheme}://" : '';

        if ($this->user) {
            $uri .= $this->user;
            if ($this->password !== null) {
                $uri .= ":{$this->password}";
            }
            $uri .= '@';
        }

        $uri .= $this->host;

        if ($this->port !== null) {
            $uri .= ":{$this->port}";
        }

        $uri .= '/' . ltrim($this->path, '/');

        $query = $this->buildQuery();
        if ($query) {
            $uri .= '?' . urldecode($query);
        }

        if ($this->fragment) {
            $uri .= '#' . rawurlencode($this->fragment);
        }

        return $uri;
    }
}
